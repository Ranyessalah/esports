import joblib
import sys
import numpy as np
from sqlalchemy import create_engine
import pandas as pd
import os
import sys

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
# charger modèles
model1 = joblib.load(os.path.join(BASE_DIR, "model_team1.pkl"))
model2 = joblib.load(os.path.join(BASE_DIR, "model_team2.pkl"))


# recevoir les id équipes depuis Symfony
team1_id = int(sys.argv[1])
team2_id = int(sys.argv[2])

# connexion DB (METS LE MEME QUE SYMFONY)
engine = create_engine("mysql+pymysql://root:@127.0.0.1:3306/esports_db")

def team_stats(team_id):
    query = f"""
    SELECT 
    AVG(score_equipe1) as avg_scored,
    AVG(score_equipe2) as avg_conceded,
    COUNT(*) as matches
    FROM matchs
    WHERE equipe1_id={team_id}
    AND statut='termine'
    """
    return pd.read_sql(query, engine).iloc[0]

t1 = team_stats(team1_id)
t2 = team_stats(team2_id)

features = np.array([[
    t1['avg_scored'], t1['avg_conceded'], t1['matches'],
    t2['avg_scored'], t2['avg_conceded'], t2['matches']
]])

score1 = round(model1.predict(features)[0])
score2 = round(model2.predict(features)[0])

print(f"{score1}-{score2}")
sys.stdout.flush()