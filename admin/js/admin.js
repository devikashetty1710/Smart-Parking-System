// Admin Data
let adminData = {
    totalSpaces: 0,
    occupiedSpaces: 0,
    totalUsers: 0,
    todayRevenue: 0,
    recentActivities: []
};

// Initialize admin dashboard
function initializeDashboard() {
    // Check if user is admin
    const session = JSON.parse(localStorage.getItem('session') || sessionStorage.getItem('session'));
    if (!session || !session.isAdmin) {
        window.location.href = '../index.html';
        return;
    }

    // Update admin name
    document.getElementById('adminName').textContent = session.name;

    // Load dashboard data
    loadDashboardData();
    loadRecentActivities();
}

// Load dashboard statistics
function loadDashboardData() {
    // In a real application, this would be fetched from a backend API
    const parkingSpaces = JSON.parse(localStorage.getItem('parkingSpaces')) || [];
    const users = JSON.parse(localStorage.getItem('users')) || [];

    adminData.totalSpaces = parkingSpaces.length;
    adminData.occupiedSpaces = parkingSpaces.filter(space => space.isOccupied).length;
    adminData.totalUsers = users.length;
    adminData.todayRevenue = calculateTodayRevenue();

    updateStatistics();
}

// Calculate today's revenue
function calculateTodayRevenue() {
    // In a real application, this would be calculated from actual transactions
    const today = new Date().toDateString();
    const transactions = JSON.parse(localStorage.getItem('transactions')) || [];
    
    return transactions
        .filter(t => new Date(t.timestamp).toDateString() === today)
        .reduce((sum, t) => sum + t.amount, 0);
}

// Update statistics display
function updateStatistics() {
    document.getElementById('totalSpaces').textContent = adminData.totalSpaces;
    document.getElementById('occupiedSpaces').textContent = adminData.occupiedSpaces;
    document.getElementById('totalUsers').textContent = adminData.totalUsers;
    document.getElementById('todayRevenue').textContent = `$${adminData.todayRevenue.toFixed(2)}`;
}

// Load recent activities
function loadRecentActivities() {
    const activityList = document.getElementById('recentActivity');
    activityList.innerHTML = '';

    // In a real application, this would be fetched from a backend API
    const activities = [
        {
            type: 'parking',
            icon: 'fa-car',
            title: 'New Parking Space Added',
            description: 'Space #25 was added to the system',
            timestamp: new Date().toISOString()
        },
        {
            type: 'user',
            icon: 'fa-user',
            title: 'New User Registration',
            description: 'John Doe registered as a new user',
            timestamp: new Date(Date.now() - 3600000).toISOString()
        },
        {
            type: 'payment',
            icon: 'fa-money-bill',
            title: 'Payment Received',
            description: 'Payment of $10.00 received for space #12',
            timestamp: new Date(Date.now() - 7200000).toISOString()
        }
    ];

    activities.forEach(activity => {
        const activityItem = document.createElement('div');
        activityItem.className = 'activity-item';
        activityItem.innerHTML = `
            <div class="activity-icon">
                <i class="fas ${activity.icon}"></i>
            </div>
            <div class="activity-details">
                <h4>${activity.title}</h4>
                <p>${activity.description}</p>
                <small>${new Date(activity.timestamp).toLocaleString()}</small>
            </div>
        `;
        activityList.appendChild(activityItem);
    });
}

// Handle admin logout
function handleLogout() {
    localStorage.removeItem('session');
    sessionStorage.removeItem('session');
    window.location.href = '../index.html';
}

// Initialize dashboard when page loads
document.addEventListener('DOMContentLoaded', initializeDashboard);

// Add admin-specific styles
const style = document.createElement('style');
style.textContent = `
    .admin-container {
        min-height: 100vh;
    }

    .admin-sidebar {
        background-color: #2c3e50;
        color: white;
        width: 250px;
        position: fixed;
        height: 100vh;
        overflow-y: auto;
    }

    .admin-main {
        margin-left: 250px;
        padding: 20px;
    }

    @media (max-width: 768px) {
        .admin-sidebar {
            width: 100%;
            height: auto;
            position: relative;
        }

        .admin-main {
            margin-left: 0;
        }
    }
`;
document.head.appendChild(style); 