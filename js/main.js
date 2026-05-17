// Parking System Data
let parkingSpaces = [];
let totalSpaces = 0;
let availableSpaces = 0;
let occupiedSpaces = 0;

// DOM Elements
const parkingGrid = document.querySelector('.parking-grid');
const totalSpacesElement = document.getElementById('total-spaces');
const availableSpacesElement = document.getElementById('available-spaces');
const occupiedSpacesElement = document.getElementById('occupied-spaces');

// Initialize the parking system
function initializeParkingSystem() {
    // Create initial parking spaces (example: 20 spaces)
    for (let i = 1; i <= 20; i++) {
        parkingSpaces.push({
            id: i,
            isOccupied: false,
            vehicleNumber: null,
            entryTime: null
        });
    }
    totalSpaces = parkingSpaces.length;
    availableSpaces = totalSpaces;
    updateStatistics();
    renderParkingSpaces();
}

// Render parking spaces in the grid
function renderParkingSpaces() {
    parkingGrid.innerHTML = '';
    parkingSpaces.forEach(space => {
        const spaceElement = document.createElement('div');
        spaceElement.className = `parking-space ${space.isOccupied ? 'occupied' : 'available'}`;
        spaceElement.innerHTML = `
            <div class="space-number">${space.id}</div>
            <div class="space-status">${space.isOccupied ? 'Occupied' : 'Available'}</div>
            ${space.isOccupied ? `<div class="vehicle-info">${space.vehicleNumber}</div>` : ''}
        `;
        spaceElement.addEventListener('click', () => handleSpaceClick(space));
        parkingGrid.appendChild(spaceElement);
    });
}

// Handle parking space click
function handleSpaceClick(space) {
    if (space.isOccupied) {
        // Handle space vacating
        if (confirm(`Do you want to vacate space ${space.id}?`)) {
            vacateSpace(space.id);
        }
    } else {
        // Handle space occupation
        const vehicleNumber = prompt('Enter vehicle number:');
        if (vehicleNumber) {
            occupySpace(space.id, vehicleNumber);
        }
    }
}

// Occupy a parking space
function occupySpace(spaceId, vehicleNumber) {
    const space = parkingSpaces.find(s => s.id === spaceId);
    if (space && !space.isOccupied) {
        space.isOccupied = true;
        space.vehicleNumber = vehicleNumber;
        space.entryTime = new Date();
        availableSpaces--;
        occupiedSpaces++;
        updateStatistics();
        renderParkingSpaces();
    }
}

// Vacate a parking space
function vacateSpace(spaceId) {
    const space = parkingSpaces.find(s => s.id === spaceId);
    if (space && space.isOccupied) {
        space.isOccupied = false;
        space.vehicleNumber = null;
        space.entryTime = null;
        availableSpaces++;
        occupiedSpaces--;
        updateStatistics();
        renderParkingSpaces();
    }
}

// Update statistics display
function updateStatistics() {
    totalSpacesElement.textContent = totalSpaces;
    availableSpacesElement.textContent = availableSpaces;
    occupiedSpacesElement.textContent = occupiedSpaces;
}

// Add new parking space
function addParkingSpace() {
    const newSpaceId = parkingSpaces.length + 1;
    parkingSpaces.push({
        id: newSpaceId,
        isOccupied: false,
        vehicleNumber: null,
        entryTime: null
    });
    totalSpaces++;
    availableSpaces++;
    updateStatistics();
    renderParkingSpaces();
}

// Remove parking space
function removeParkingSpace() {
    if (parkingSpaces.length > 0) {
        const lastSpace = parkingSpaces[parkingSpaces.length - 1];
        if (lastSpace.isOccupied) {
            alert('Cannot remove occupied space!');
            return;
        }
        parkingSpaces.pop();
        totalSpaces--;
        availableSpaces--;
        updateStatistics();
        renderParkingSpaces();
    }
}

// Event Listeners for Admin Controls
document.querySelectorAll('.control-btn').forEach(button => {
    button.addEventListener('click', (e) => {
        switch (e.target.textContent) {
            case 'Add Space':
                addParkingSpace();
                break;
            case 'Remove Space':
                removeParkingSpace();
                break;
            case 'View Statistics':
                // Implement statistics view
                break;
        }
    });
});

// Initialize the system when the page loads
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dynamic elements once DOM is loaded
    initializeDynamicElements();
    
    // Handle parking space search
    initializeSearch();
});

function initializeDynamicElements() {
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Initialize parking space count display if it exists
    const totalSpaces = document.getElementById('total-spaces');
    const availableSpaces = document.getElementById('available-spaces');
    const occupiedSpaces = document.getElementById('occupied-spaces');
    
    if (totalSpaces && availableSpaces && occupiedSpaces) {
        // Fetch parking statistics via AJAX
        fetchParkingStats()
            .then(stats => {
                totalSpaces.textContent = stats.total || 0;
                availableSpaces.textContent = stats.available || 0;
                occupiedSpaces.textContent = stats.occupied || 0;
            })
            .catch(error => console.error('Error fetching parking stats:', error));
    }
}

function initializeSearch() {
    const searchBtn = document.getElementById('search-btn');
    const locationSearch = document.getElementById('location-search');
    
    if (searchBtn && locationSearch) {
        searchBtn.addEventListener('click', function() {
            const query = locationSearch.value.trim();
            if (query) {
                searchParkingSpaces(query);
            }
        });
        
        // Enable search on Enter key
        locationSearch.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = this.value.trim();
                if (query) {
                    searchParkingSpaces(query);
                }
            }
        });
    }
}

function searchParkingSpaces(query) {
    const parkingGrid = document.querySelector('.parking-grid');
    
    if (parkingGrid) {
        // Show loading indicator
        parkingGrid.innerHTML = '<div class="loading">Searching...</div>';
        
        // Fetch parking spaces that match the query
        fetch(`api/search_spaces.php?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.spaces && data.spaces.length > 0) {
                    parkingGrid.innerHTML = '';
                    
                    // Create HTML for each parking space
                    data.spaces.forEach(space => {
                        const spaceElement = document.createElement('div');
                        spaceElement.className = 'parking-space';
                        
                        spaceElement.innerHTML = `
                            <div class="space-number">${space.space_name}</div>
                            <div class="space-details">
                                <p><i class="fas fa-map-marker-alt"></i> ${space.location}</p>
                                <p><i class="fas fa-dollar-sign"></i> $${parseFloat(space.price_per_hour).toFixed(2)}/hour</p>
                            </div>
                            <button class="reserve-btn" onclick="window.location.href='reserve.php?id=${space.space_id}'">Reserve</button>
                        `;
                        
                        parkingGrid.appendChild(spaceElement);
                    });
                } else {
                    parkingGrid.innerHTML = '<p class="no-spaces">No parking spaces found matching your search.</p>';
                }
            })
            .catch(error => {
                console.error('Error searching for parking spaces:', error);
                parkingGrid.innerHTML = '<p class="no-spaces">Error searching for spaces. Please try again.</p>';
            });
    }
}

function fetchParkingStats() {
    // This would be replaced with a real API call in production
    return new Promise((resolve, reject) => {
        // Simulate API call with a timeout
        setTimeout(() => {
            // For demo purposes, we're returning dummy data
            // In a real application, this would be an AJAX call to your API
            resolve({
                total: 50,
                available: 35,
                occupied: 15
            });
        }, 500);
    });
}

// Add styles for parking spaces
const style = document.createElement('style');
style.textContent = `
    .parking-space {
        background-color: #fff;
        border: 2px solid #3498db;
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .parking-space:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .parking-space.occupied {
        background-color: #e74c3c;
        color: white;
    }

    .parking-space.available {
        background-color: #2ecc71;
        color: white;
    }

    .space-number {
        font-size: 1.5rem;
        font-weight: bold;
    }

    .space-status {
        margin: 0.5rem 0;
    }

    .vehicle-info {
        font-size: 0.9rem;
        margin-top: 0.5rem;
    }
`;
document.head.appendChild(style); 