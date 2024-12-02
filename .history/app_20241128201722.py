from flask import Flask, request, jsonify
from tensorflow.keras.models import load_model
import numpy as np
from PIL import Image
import io

app = Flask(__name__)

# Charger le modèle de reconnaissance faciale
model = load_model("face_recog_vgg_new.keras")

# Liste simulée des employés avec leurs données
employees = {
    1: {"id": 1, "firstName": "John", "lastName": "Doe", "photoPath": "/images/employees/john_doe.jpg"},
    2: {"id": 2, "firstName": "Jane", "lastName": "Smith", "photoPath": "/images/employees/jane_smith.jpg"},
}

@app.route("/predict", methods=["POST"])
def predict():
    try:
        # Récupérer l'image envoyée dans la requête
        file = request.files.get('image')
        if not file:
            return jsonify({"message": "Aucune image reçue"}), 400

        # Prétraiter l'image pour le modèle
        image = Image.open(io.BytesIO(file.read())).convert("RGB")
        image = image.resize((224, 224))  # Taille d'entrée du modèle
        image_array = np.array(image) / 255.0
        image_array = np.expand_dims(image_array, axis=0)

        # Faire une prédiction avec le modèle
        predictions = model.predict(image_array)
        predicted_class = np.argmax(predictions, axis=1)[0]

        # Vérifier si l'employé existe
        if predicted_class in employees:
            return jsonify({
                "message": "Employé reconnu",
                "employee": employees[predicted_class]
            })
        else:
            return jsonify({"message": "Employé non trouvé"}), 404

    except Exception as e:
        return jsonify({"message": f"Erreur interne : {str(e)}"}), 500

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000)
