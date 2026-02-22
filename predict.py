import joblib
import sys
import numpy as np
from sqlalchemy import create_engine
import pandas as pd
import os

# ---------------------------
# CONFIG
# ---------------------------
BASE_DIR = os.path.dirname(os.path.abspath(__file__))

MODEL1_PATH = os.path.join(BASE_DIR, "model_team1.pkl")
MODEL2_PATH = os.path.join(BASE_DIR, "model_team2.pkl")

DB_URL = "mysql+pymysql://root:@127.0.0.1:3306/esports_db"


# ---------------------------
# SAFE PRINT (for Symfony)
# ---------------------------
def output(msg):
    print(msg)
    sys.stdout.flush()


# ---------------------------
# CHECK ARGUMENTS
# ---------------------------
if len(sys.argv) < 3:
    output("PYTHON_ERROR: Missing team ids")
    sys.exit(1)

try:
    team1_id = int(sys.argv[1])
    team2_id = int(sys.argv[2])
except ValueError:
    output("PYTHON_ERROR: Invalid team id")
    sys.exit(1)


# ---------------------------
# LOAD MODELS SAFELY
# ---------------------------
if not os.path.exists(MODEL1_PATH) or not os.path.exists(MODEL2_PATH):
    output("PYTHON_ERROR: Models not trained yet")
    sys.exit(1)

try:
    model1 = joblib.load(MODEL1_PATH)
    model2 = joblib.load(MODEL2_PATH)
except Exception as e:
    output(f"PYTHON_ERROR: Cannot load model - {str(e)}")
    sys.exit(1)


# ---------------------------
# CONNECT DATABASE
# ---------------------------
try:
    engine = create_engine(DB_URL, pool_pre_ping=True)
except Exception as e:
    output(f"PYTHON_ERROR: DB connection failed - {str(e)}")
    sys.exit(1)


# ---------------------------
# TEAM STATISTICS (SMART VERSION)
# counts home + away matches
# ---------------------------
def team_stats(team_id):

    query = f"""
    SELECT 
    COALESCE(AVG(CASE 
        WHEN equipe1_id={team_id} THEN score_equipe1
        ELSE score_equipe2 END),0) AS avg_scored,

    COALESCE(AVG(CASE 
        WHEN equipe1_id={team_id} THEN score_equipe2
        ELSE score_equipe1 END),0) AS avg_conceded,

    COUNT(*) AS matches
    FROM matchs
    WHERE (equipe1_id={team_id} OR equipe2_id={team_id})
    AND statut='termine'
    """

    try:
        df = pd.read_sql(query, engine)

        if df.empty:
            return [0.0, 0.0, 0.0]

        row = df.iloc[0]

        return [
            float(row['avg_scored']),
            float(row['avg_conceded']),
            float(row['matches'])
        ]

    except Exception as e:
        output(f"PYTHON_ERROR: SQL failed - {str(e)}")
        sys.exit(1)


# ---------------------------
# GET FEATURES
# ---------------------------
t1 = team_stats(team1_id)
t2 = team_stats(team2_id)

features = np.array([[*t1, *t2]])

# replace NaN if any
features = np.nan_to_num(features)


# ---------------------------
# PREDICT
# ---------------------------
try:
    score1 = max(0, round(float(model1.predict(features)[0])))
    score2 = max(0, round(float(model2.predict(features)[0])))

    # limit crazy scores
    score1 = min(score1, 20)
    score2 = min(score2, 20)

    output(f"{score1}-{score2}")

except Exception as e:
    output(f"PYTHON_ERROR: Prediction failed - {str(e)}")
    sys.exit(1)