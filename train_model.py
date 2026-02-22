import pandas as pd
import numpy as np
from sqlalchemy import create_engine
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestRegressor
import joblib

# connexion a ta base Symfony
engine = create_engine("mysql+pymysql://root:@127.0.0.1:3306/esports_db")

# récupérer les matchs terminés
query = """
SELECT 
m.id,
m.score_equipe1,
m.score_equipe2,
m.equipe1_id,
m.equipe2_id
FROM matchs m
WHERE m.statut='termine'
"""

matches = pd.read_sql(query, engine)

# fonction pour calculer stats équipe
def team_stats(team_id):
    q = f"""
    SELECT 
    AVG(score_equipe1) as avg_scored,
    AVG(score_equipe2) as avg_conceded,
    COUNT(*) as matches
    FROM matchs
    WHERE equipe1_id={team_id}
    """
    return pd.read_sql(q, engine).iloc[0]

X = []
y1 = []
y2 = []

for _, row in matches.iterrows():
    t1 = team_stats(row['equipe1_id'])
    t2 = team_stats(row['equipe2_id'])

    features = [
        t1['avg_scored'], t1['avg_conceded'], t1['matches'],
        t2['avg_scored'], t2['avg_conceded'], t2['matches']
    ]

    X.append(features)
    y1.append(row['score_equipe1'])
    y2.append(row['score_equipe2'])

X = np.array(X)
y1 = np.array(y1)
y2 = np.array(y2)

# split
X_train, X_test, y1_train, y1_test, y2_train, y2_test = train_test_split(
    X, y1, y2, test_size=0.2, random_state=42
)
# modèles
model_team1 = RandomForestRegressor(n_estimators=200)
model_team2 = RandomForestRegressor(n_estimators=200)

model_team1.fit(X_train, y1_train)
model_team2.fit(X_train, y2_train)


# sauvegarde
joblib.dump(model_team1, "model_team1.pkl")
joblib.dump(model_team2, "model_team2.pkl")

print("MODEL TRAINED SUCCESSFULLY")
# ===== ELO SYSTEM =====
