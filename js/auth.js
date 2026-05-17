// User data storage (in a real application, this would be handled by a backend)
let users = JSON.parse(localStorage.getItem('users')) || [];

// Login handler
function handleLogin(event) {
    event.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const rememberMe = document.querySelector('input[name="remember"]').checked;

    // Find user in the database
    const user = users.find(u => u.email === email && u.password === password);

    if (user) {
        // Store user session
        const session = {
            email: user.email,
            name: user.fullName,
            timestamp: new Date().getTime()
        };

        if (rememberMe) {
            localStorage.setItem('session', JSON.stringify(session));
        } else {
            sessionStorage.setItem('session', JSON.stringify(session));
        }

        // Redirect to home page
        window.location.href = 'index.html';
    } else {
        showError('Invalid email or password');
    }
}

// Register handler
function handleRegister(event) {
    event.preventDefault();
    
    const fullName = document.getElementById('fullName').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    // Validate passwords match
    if (password !== confirmPassword) {
        showError('Passwords do not match');
        return;
    }

    // Check if user already exists
    if (users.some(u => u.email === email)) {
        showError('Email already registered');
        return;
    }

    // Create new user
    const newUser = {
        fullName,
        email,
        phone,
        password,
        createdAt: new Date().getTime()
    };

    // Add user to database
    users.push(newUser);
    localStorage.setItem('users', JSON.stringify(users));

    // Show success message and redirect to login
    showSuccess('Registration successful! Redirecting to login...');
    setTimeout(() => {
        window.location.href = 'login.html';
    }, 2000);
}

// Show error message
function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';

    const form = document.querySelector('form');
    form.insertBefore(errorDiv, form.firstChild);

    // Remove error message after 3 seconds
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
}

// Check if user is logged in
function checkAuth() {
    const session = JSON.parse(localStorage.getItem('session') || sessionStorage.getItem('session'));
    if (session) {
        // Update UI for logged in user
        const authButtons = document.querySelector('.auth-buttons');
        if (authButtons) {
            authButtons.innerHTML = `
                <span class="user-greeting">Welcome, ${session.name}</span>
                <button class="logout-btn" onclick="handleLogout()">Logout</button>
            `;
        }
    }
}

// Handle logout
function handleLogout() {
    localStorage.removeItem('session');
    sessionStorage.removeItem('session');
    window.location.href = 'index.html';
}

// Initialize auth check when page loads
document.addEventListener('DOMContentLoaded', checkAuth);

// Add styles for auth elements
const style = document.createElement('style');
style.textContent = `
    .user-greeting {
        color: white;
        margin-right: 1rem;
    }

    .logout-btn {
        padding: 0.5rem 1rem;
        background-color: #e74c3c;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .logout-btn:hover {
        background-color: #c0392b;
    }
`;
document.head.appendChild(style); 