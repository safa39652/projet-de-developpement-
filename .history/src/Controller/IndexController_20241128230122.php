<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Reconnaissance Faciale</title>
    <style>
        /* Styles pour la page */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            margin: 20px 0;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            margin-top: 20px;
            gap: 20px;
        }

        #camera-section {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        video {
            border: 2px solid #ccc;
            border-radius: 5px;
            max-width: 400px;
            width: 100%;
        }

        button {
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        #result {
            display: none;
            text-align: center;
            max-width: 300px;
        }

        #result img {
            max-width: 100%;
            border: 2px solid #ccc;
            border-radius: 5px;
        }

        #employee-info {
            margin-top: 10px;
            font-size: 1.1em;
        }

        /* Container for top-right positioning */
        #admin-icon-container {
            position: absolute;
            top: 30px;
            right: 50px;
            z-index: 1000;
        }

        /* Icon styling */
        #admin-icon {
            width: 100px;
            height: 90px;
            cursor: pointer;
            background: none;
            border-radius: 50%;
            box-shadow: none;
        }

        /* Icon hover effect */
        #admin-icon:hover {
            transform: scale(1.1);
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
        }

    </style>
</head>
<body>
    <h1>Test Reconnaissance Faciale</h1>
    <div class="container">
        <div id="camera-section">
            <video autoplay></video>
            <button id="capture">Capturer & Vérifier</button>
        </div>

        <!-- Section Résultat -->
        <div id="result">
            <img id="employee-photo" src="public/images/employees/john_doe.jpg" alt="Photo de l'employé">
            <div id="employee-info"></div>
        </div>
    </div>

   <div id="admin-icon-container">
    <img src="/images/admin_icon.jpg" alt="Admin Icon" id="admin-icon">
</div>

    <script>
        const video = document.querySelector('video');
        const captureButton = document.querySelector('#capture');
        const resultDiv = document.querySelector('#result');
        const photoElement = document.querySelector('#employee-photo');
        const infoElement = document.querySelector('#employee-info');
        const cameraSection = document.querySelector('#camera-section');
        
        // Accéder à la caméra
navigator.mediaDevices.getUserMedia({ video: true })
    .then((stream) => {
        video.srcObject = stream;
        console.log("Accès à la caméra réussi.");
    })
    .catch((error) => {
        console.error("Erreur d'accès à la caméra :", error);
    });

    captureButton.addEventListener('click', () => {
    console.log("Capture de l'image lancée...");
    
    const canvas = document.createElement("canvas");
    const context = canvas.getContext("2d");
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    const image = canvas.toDataURL("image/jpeg");  // Image capturée en base64

    console.log("Image capturée :", image);

    if (!image) {
        alert("Aucune image capturée.");
        return;
    }

    captureButton.disabled = true;  // Désactiver le bouton après la capture

    // Vérification avant d'envoyer
    console.log("Envoi de l'image vers l'API...");
    fetch('/api/check-employee', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ image: image })
    })
    .then(response => {
        console.log("Réponse de l'API :", response);
        return response.json();
    })
    .then(data => {
        console.log("Données reçues de l'API :", data);
        captureButton.disabled = false;

        if (data.employee) {
            photoElement.src = data.employee.photoPath;
            infoElement.innerHTML = `
                <strong>ID :</strong> ${data.employee.id}<br>
                <strong>Nom :</strong> ${data.employee.firstName} ${data.employee.lastName}<br>
                <strong>Message :</strong> ${data.message}
            `;
            resultDiv.style.display = 'block';
            cameraSection.style.display = 'none';
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error("Erreur lors de la vérification : ", error);
        captureButton.disabled = false;
        alert("Erreur lors de la vérification : " + error.message);
    });
});


        // Admin icon redirection
        const adminIcon = document.querySelector('#admin-icon');
        adminIcon.addEventListener('click', () => {
            window.location.href = '/admin/login';
        });
    </script>
</body>
</html>
