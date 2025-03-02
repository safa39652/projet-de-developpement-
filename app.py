from flask import Flask, request, jsonify
from tensorflow.keras.models import load_model
import numpy as np
from PIL import Image
import io
import os
import uuid
from datetime import datetime  # Import de datetime pour récupérer la date et l'heure actuelles

app = Flask(__name__)

# Charger le modèle de reconnaissance faciale
model = load_model(r"C:\\Users\\ASUS\\Desktop\\DSIA-c\\semestre1\\projet developpement\\models\\face_recog_vgg_new.keras")

# Chemin des données d'entraînement pour obtenir les classes
dataset_dir = r"C:\Users\ASUS\Desktop\\DSIA-c\semestre1\projet developpement\dataset\train"
class_names = {i: class_name for i, class_name in enumerate(os.listdir(dataset_dir))}
print(f"Classes détectées : {class_names}")

# Créer un dossier pour stocker les images téléchargées si ce n'est pas déjà faita
UPLOAD_FOLDER = "uploads"
if not os.path.exists(UPLOAD_FOLDER):
    os.makedirs(UPLOAD_FOLDER)

@app.route("/recognize", methods=["POST"])
def predict():
    try:
        # Récupérer l'image envoyée dans la requête
        file = request.files.get('image')
        if not file:
            return jsonify({"message": "Aucune image reçue"}), 400

        # Prétraiter l'image pour le modèle
        image = Image.open(io.BytesIO(file.read())).convert("RGB")
        image = image.resize((224, 224))  # Taille d'entrée du modèle
        image_array = np.array(image) / 255.0  # Normalisation des pixels
        image_array = np.expand_dims(image_array, axis=0)  # Ajouter une dimension pour le batch

        # Faire une prédiction avec le modèle
        predictions = model.predict(image_array)
        predicted_class = np.argmax(predictions)  # Classe prédite
        confidence = predictions[0][predicted_class]

        # Convertir les valeurs en type float pour la sérialisation JSON
        confidence = float(confidence)

        # Initialisation de l'objet employee_data avant d'utiliser la variable
        employee_data = {}

        # Récupérer la date et l'heure actuelles au moment de la capture
        date_entrée = datetime.now().strftime('%Y-%m-%d %H:%M:%S')  # Format: 2024-12-04 14:30:00

        # Vérifier la confiance
        if confidence > 0.5:  # Seuil de confiance
            name = class_names[predicted_class]
            message = "Bonjour, on commence"
            employee_data = {"name": name, "confidence": confidence, "id": "12345", "message": message, "date_entrée": date_entrée}
        else:
            name = "Aucun visage correspondant"
            message = "Visage non reconnu"
            employee_data = {"name": name, "confidence": confidence, "id": "N/A", "message": message, "date_entrée": date_entrée}

        # Sauvegarder l'image dans le dossier uploads avec un nom unique
        image_filename = f"{uuid.uuid4()}.jpg"  # Utilisation de UUID pour un nom unique
        image_path = os.path.join(UPLOAD_FOLDER, image_filename)
        image.save(image_path)

        # Retourner le chemin de l'image dans la réponse
        image_url = f"http://localhost:5000/{UPLOAD_FOLDER}/{image_filename}"  # URL de l'image

        return jsonify({
            "message": "Employé reconnu" if name != "Aucun visage correspondant" else "Employé non trouvé",
            "employee": {
                "name": name,
                "confidence": confidence,
                "photoPath": image_url,
                "id": employee_data["id"],
                "message": employee_data["message"],
                "date_entrée": employee_data["date_entrée"]  # Inclure la date d'entrée dans la réponse
            }
        })

    except Exception as e:
        return jsonify({"message": f"Erreur interne : {str(e)}"}), 500

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000)