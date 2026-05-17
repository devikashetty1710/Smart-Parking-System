// API base URL
const API_BASE_URL = 'http://localhost:3000/api';

// Helper function to get auth token
function getAuthToken() {
    return localStorage.getItem('token');
}

// Helper function to get current user
function getCurrentUser() {
    const user = localStorage.getItem('currentUser');
    return user ? JSON.parse(user) : null;
}

// Helper function to make API calls
async function apiCall(endpoint, method = 'GET', data = null) {
    const url = `${API_BASE_URL}${endpoint}`;
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${getAuthToken()}`
        }
    };

    if (data) {
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(url, options);
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.error || 'API request failed');
        }

        return result;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

// Parking API functions
const parkingApi = {
    // Get all parking lots
    getParkingLots: () => apiCall('/parking/lots'),

    // Get available slots for a lot
    getAvailableSlots: (lotId) => apiCall(`/parking/lots/${lotId}/slots`),

    // Create new booking
    createBooking: (bookingData) => apiCall('/parking/bookings', 'POST', bookingData),

    // Record payment
    recordPayment: (paymentData) => apiCall('/parking/payments', 'POST', paymentData),

    // Get user's vehicles
    getVehicles: (userId) => apiCall(`/parking/users/${userId}/vehicles`),

    // Add new vehicle
    addVehicle: (vehicleData) => apiCall('/parking/vehicles', 'POST', vehicleData)
};

// Auth API functions
const authApi = {
    // Login
    login: (credentials) => apiCall('/auth/login', 'POST', credentials),

    // Register
    register: (userData) => apiCall('/auth/register', 'POST', userData),

    // Logout
    logout: () => {
        localStorage.removeItem('token');
        localStorage.removeItem('currentUser');
        window.location.href = '/login.html';
    }
};

// Export the API functions
window.api = {
    parking: parkingApi,
    auth: authApi,
    getCurrentUser,
    getAuthToken
}; 