// Modal Interaction for Adding Employees
const addEmployeeBtn = document.getElementById("add-employee-btn");
const addEmployeeModal = document.createElement("div");
addEmployeeModal.classList.add("modal", "hidden");
addEmployeeModal.innerHTML = `
  <h2>Ajouter un Employé</h2>
  <form id="add-employee-form">
    <label for="employee-name">Nom :</label>
    <input type="text" id="employee-name" name="employee-name" required />
    <label for="employee-role">Poste :</label>
    <input type="text" id="employee-role" name="employee-role" required />
    <label for="employee-status">Statut :</label>
    <select id="employee-status" name="employee-status">
      <option value="Actif">Actif</option>
      <option value="Inactif">Inactif</option>
    </select>
    <button type="submit">Ajouter</button>
    <button type="button" class="close-modal">Annuler</button>
  </form>
`;
document.body.appendChild(addEmployeeModal);

// Open Modal
addEmployeeBtn.addEventListener("click", () => {
  addEmployeeModal.classList.remove("hidden");
});

// Close Modal
document.querySelectorAll(".close-modal").forEach((button) => {
  button.addEventListener("click", () => {
    addEmployeeModal.classList.add("hidden");
  });
});

// Add New Employee to Table
document.getElementById("add-employee-form").addEventListener("submit", (e) => {
  e.preventDefault();
  const name = document.getElementById("employee-name").value;
  const role = document.getElementById("employee-role").value;
  const status = document.getElementById("employee-status").value;

  const tableBody = document.querySelector("#employees table tbody");
  const newRow = document.createElement("tr");
  newRow.innerHTML = `
    <td>${tableBody.rows.length + 1}</td>
    <td>${name}</td>
    <td>${role}</td>
    <td>${status}</td>
  `;
  tableBody.appendChild(newRow);

  // Close Modal
  addEmployeeModal.classList.add("hidden");
});

// Chart.js Initialization for Employee Activity
const employeeActivityCtx = document
  .getElementById("employee-activity-chart")
  .getContext("2d");
const alertStatusCtx = document
  .getElementById("alert-status-chart")
  .getContext("2d");

new Chart(employeeActivityCtx, {
  type: "bar",
  data: {
    labels: ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi"],
    datasets: [
      {
        label: "Activité Employés",
        data: [12, 19, 8, 15, 5],
        backgroundColor: "rgba(75, 192, 192, 0.2)",
        borderColor: "rgba(75, 192, 192, 1)",
        borderWidth: 1,
      },
    ],
  },
  options: {
    scales: {
      y: {
        beginAtZero: true,
      },
    },
  },
});

// Chart.js Initialization for Alert Status
new Chart(alertStatusCtx, {
  type: "pie",
  data: {
    labels: ["Résolues", "En Attente"],
    datasets: [
      {
        data: [8, 2],
        backgroundColor: ["#4caf50", "#ff9800"],
      },
    ],
  },
});

// Simulated Notifications Update
const notificationsList = document.querySelector("#notifications ul");
const addNotification = (message) => {
  const newNotification = document.createElement("li");
  newNotification.textContent = message;
  notificationsList.appendChild(newNotification);
};

// Example of Adding a New Notification
addNotification(
  "Nouvelle alerte : Vérifiez la connexion réseau de l'employé #3."
);

// Form Validation for Sign-In and Sign-Up
document.getElementById("sign-in-form").addEventListener("submit", (e) => {
  e.preventDefault();
  const email = document.getElementById("email").value;
  const password = document.getElementById("password").value;
  if (email && password) {
    alert("Connexion réussie !");
  } else {
    alert("Veuillez remplir tous les champs.");
  }
});

document.getElementById("sign-up-form").addEventListener("submit", (e) => {
  e.preventDefault();
  const name = document.getElementById("name").value;
  const email = document.getElementById("email").value;
  const password = document.getElementById("password").value;
  if (name && email && password) {
    alert("Inscription réussie !");
  } else {
    alert("Veuillez remplir tous les champs.");
  }
});

// Profile Edit Feature
const editProfileBtn = document.getElementById("edit-profile-btn");
editProfileBtn.addEventListener("click", () => {
  const newName = prompt("Entrez votre nouveau nom :", "Admin Name");
  if (newName) {
    document.querySelector(
      "#admin-profile .profile-details p strong"
    ).textContent = newName;
    document.getElementById("admin-name").textContent = newName;
    alert("Profil mis à jour avec succès !");
  }
});

document
  .getElementById("toggle-view-btn")
  .addEventListener("click", function () {
    // Changer les données du graphique ou la vue
    chart.data.datasets[0].data = [20, 30, 15, 10, 40]; // Exemples de nouvelles données
    chart.update(); // Mettre à jour le graphique
  });










  //cette partie

document
  .getElementById("addEmployeForm")
  .addEventListener("submit", function (e) {
    e.preventDefault(); // Empêcher le rechargement de la page lors de la soumission du formulaire

    // Récupérer les valeurs du formulaire
    const data = {
      nom: document.getElementById("nom").value,
      poste: document.getElementById("poste").value,
    };

    // Effectuer la requête AJAX vers l'API d'ajout d'employé
    fetch("/api/employe/add", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    })
      .then((response) => response.json())
      .then((employe) => {
        // Ajouter dynamiquement l'employé dans le tableau du tableau de bord
        const tableBody = document.querySelector("table tbody");
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${employe.nom}</td>
            <td>${employe.poste}</td>
            <td>
                <a href="/employe/${employe.id}" class="btn btn-primary btn-sm">Voir</a>
                <a href="/employe/edit/${employe.id}" class="btn btn-warning btn-sm">Modifier</a>
                <a href="/employe/delete/${employe.id}" class="btn btn-danger btn-sm">Supprimer</a>
            </td>
        `;
        tableBody.appendChild(row);
      })
      .catch((error) => console.error("Erreur :", error));
  });
