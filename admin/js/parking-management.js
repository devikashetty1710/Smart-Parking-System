// Parking Management Functions
function initializeParkingManagement() {
    // Check if user is admin
    const session = JSON.parse(localStorage.getItem('session') || sessionStorage.getItem('session'));
    if (!session || !session.isAdmin) {
        window.location.href = '../index.html';
        return;
    }

    // Update admin name
    document.getElementById('adminName').textContent = session.name;

    // Load parking spaces
    loadParkingSpaces();

    // Initialize search functionality
    document.getElementById('searchSpace').addEventListener('input', handleSearch);
}

// Load parking spaces
function loadParkingSpaces() {
    const spacesList = document.getElementById('spacesList');
    spacesList.innerHTML = '';

    // Get spaces from localStorage
    const parkingSpaces = JSON.parse(localStorage.getItem('parkingSpaces')) || [];

    if (parkingSpaces.length === 0) {
        spacesList.innerHTML = '<p class="no-spaces">No parking spaces found</p>';
        return;
    }

    parkingSpaces.forEach(space => {
        const spaceElement = document.createElement('div');
        spaceElement.className = `space-item ${space.isOccupied ? 'occupied' : 'available'}`;
        spaceElement.innerHTML = `
            <div class="space-info">
                <h3>Space #${space.id}</h3>
                <p>Type: ${space.type}</p>
                <p>Location: ${space.location}</p>
                <p>Status: ${space.isOccupied ? 'Occupied' : 'Available'}</p>
            </div>
            <div class="space-actions">
                <button class="action-btn edit-btn" onclick="editSpace(${space.id})">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="action-btn delete-btn" onclick="deleteSpace(${space.id})">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        `;
        spacesList.appendChild(spaceElement);
    });
}

// Handle adding new space
function handleAddSpace(event) {
    event.preventDefault();

    const spaceNumber = document.getElementById('spaceNumber').value;
    const spaceType = document.getElementById('spaceType').value;
    const spaceLocation = document.getElementById('spaceLocation').value;

    // Get existing spaces
    const parkingSpaces = JSON.parse(localStorage.getItem('parkingSpaces')) || [];

    // Check if space number already exists
    if (parkingSpaces.some(space => space.id === spaceNumber)) {
        showError('Space number already exists');
        return;
    }

    // Create new space
    const newSpace = {
        id: spaceNumber,
        type: spaceType,
        location: spaceLocation,
        isOccupied: false,
        createdAt: new Date().getTime()
    };

    // Add to spaces array
    parkingSpaces.push(newSpace);
    localStorage.setItem('parkingSpaces', JSON.stringify(parkingSpaces));

    // Show success message and reload spaces
    showSuccess('Parking space added successfully');
    loadParkingSpaces();

    // Reset form
    event.target.reset();
}

// Handle space editing
function editSpace(spaceId) {
    const parkingSpaces = JSON.parse(localStorage.getItem('parkingSpaces')) || [];
    const space = parkingSpaces.find(s => s.id === spaceId);

    if (!space) {
        showError('Space not found');
        return;
    }

    // In a real application, this would open a modal or form for editing
    const newType = prompt('Enter new space type:', space.type);
    const newLocation = prompt('Enter new location:', space.location);

    if (newType && newLocation) {
        space.type = newType;
        space.location = newLocation;
        localStorage.setItem('parkingSpaces', JSON.stringify(parkingSpaces));
        showSuccess('Space updated successfully');
        loadParkingSpaces();
    }
}

// Handle space deletion
function deleteSpace(spaceId) {
    if (!confirm('Are you sure you want to delete this space?')) {
        return;
    }

    const parkingSpaces = JSON.parse(localStorage.getItem('parkingSpaces')) || [];
    const spaceIndex = parkingSpaces.findIndex(s => s.id === spaceId);

    if (spaceIndex === -1) {
        showError('Space not found');
        return;
    }

    // Check if space is occupied
    if (parkingSpaces[spaceIndex].isOccupied) {
        showError('Cannot delete occupied space');
        return;
    }

    // Remove space
    parkingSpaces.splice(spaceIndex, 1);
    localStorage.setItem('parkingSpaces', JSON.stringify(parkingSpaces));
    showSuccess('Space deleted successfully');
    loadParkingSpaces();
}

// Handle space search
function handleSearch(event) {
    const searchTerm = event.target.value.toLowerCase();
    const spaces = document.querySelectorAll('.space-item');

    spaces.forEach(space => {
        const spaceText = space.textContent.toLowerCase();
        space.style.display = spaceText.includes(searchTerm) ? 'flex' : 'none';
    });
}

// Show error message
function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';

    const form = document.querySelector('form');
    form.insertBefore(errorDiv, form.firstChild);

    setTimeout(() => {
        errorDiv.remove();
    }, 3000);
}

// Show success message
function showSuccess(message) {
    const successDiv = document.createElement('div');
    successDiv.className = 'success-message';
    successDiv.textContent = message;
    successDiv.style.display = 'block';

    const form = document.querySelector('form');
    form.insertBefore(successDiv, form.firstChild);

    setTimeout(() => {
        successDiv.remove();
    }, 3000);
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', initializeParkingManagement);

// Add styles for parking management
const style = document.createElement('style');
style.textContent = `
    .parking-management-content {
        padding: 1rem;
    }

    .management-section {
        background-color: white;
        padding: 1.5rem;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .search-bar {
        display: flex;
        margin-bottom: 1rem;
    }

    .search-bar input {
        flex: 1;
        padding: 0.8rem;
        border: 1px solid #ddd;
        border-radius: 5px 0 0 5px;
    }

    .search-btn {
        padding: 0.8rem 1.5rem;
        background-color: #3498db;
        color: white;
        border: none;
        border-radius: 0 5px 5px 0;
        cursor: pointer;
    }

    .spaces-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1rem;
    }

    .space-item {
        background-color: white;
        padding: 1rem;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .space-item.occupied {
        border-left: 4px solid #e74c3c;
    }

    .space-item.available {
        border-left: 4px solid #2ecc71;
    }

    .space-info h3 {
        margin: 0;
        color: #2c3e50;
    }

    .space-info p {
        margin: 0.5rem 0;
        color: #666;
    }

    .space-actions {
        display: flex;
        gap: 0.5rem;
    }

    .edit-btn {
        background-color: #3498db;
    }

    .delete-btn {
        background-color: #e74c3c;
    }

    .no-spaces {
        text-align: center;
        color: #666;
        padding: 2rem;
    }
`;
document.head.appendChild(style); 