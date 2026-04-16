# Module Bateau – Smart Fishing Port Management

## 📌 Description
The Bateau module manages all fishing boats registered in the port.
It allows CRUD operations and ensures each boat is properly tracked inside the system.

## 🎯 Responsibilities
- Add a new boat
- Modify boat information
- Delete a boat
- Display all boats
- Search boats by name or registration number

## 🗂️ Main Entities
- numero_carte_grise
- nom
- status
- date_arrivage

## 🔗 Relationships
- A boat can be assigned to one emplacement
- A boat can have inspections
- A boat can have scheduled departures

## 🖥️ UI Components
- QTableWidget for displaying boats
- QLineEdit for search
- QPushButton (Ajouter, Modifier, Supprimer)
- QDateEdit for date management

## ⚙️ Business Logic
- Prevent duplicate registration numbers
- Validate required fields
- Auto-update status based on inspection results

## 👤 Responsible
Ahmed Saadaoui
