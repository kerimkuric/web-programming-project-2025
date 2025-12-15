// Authentication Service
const AuthService = {
    TOKEN_KEY: 'lms_token',
    USER_KEY: 'lms_user',
    
    // Get stored token
    getToken() {
        return localStorage.getItem(this.TOKEN_KEY);
    },
    
    // Get stored user
    getUser() {
        const userStr = localStorage.getItem(this.USER_KEY);
        return userStr ? JSON.parse(userStr) : null;
    },
    
    // Check if user is authenticated
    isAuthenticated() {
        return !!this.getToken();
    },
    
    // Check if user is admin
    isAdmin() {
        const user = this.getUser();
        return user && user.is_admin === true;
    },
    
    // Login
    async login(email, password) {
        try {
            const response = await fetch('http://localhost/library-management-system-2025/backend/index.php/api/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, password })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Store token and user
                localStorage.setItem(this.TOKEN_KEY, data.data.token);
                localStorage.setItem(this.USER_KEY, JSON.stringify(data.data.user));
                return { success: true, user: data.data.user };
            } else {
                return { success: false, message: data.message || 'Login failed' };
            }
        } catch (error) {
            console.error('Login error:', error);
            return { success: false, message: 'Network error. Please try again.' };
        }
    },
    
    // Register
    async register(userData) {
        try {
            const response = await fetch('http://localhost/library-management-system-2025/backend/index.php/api/auth/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(userData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                return { success: true, message: 'Registration successful. Please login.' };
            } else {
                return { success: false, message: data.message || 'Registration failed' };
            }
        } catch (error) {
            console.error('Register error:', error);
            return { success: false, message: 'Network error. Please try again.' };
        }
    },
    
    // Logout
    logout() {
        localStorage.removeItem(this.TOKEN_KEY);
        localStorage.removeItem(this.USER_KEY);
        window.location.hash = '#/login';
    },
    
    // Get current user from server (refresh)
    async getCurrentUser() {
        try {
            const token = this.getToken();
            if (!token) {
                return null;
            }
            
            const response = await fetch('http://localhost/library-management-system-2025/backend/index.php/api/auth/me', {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                localStorage.setItem(this.USER_KEY, JSON.stringify(data.data));
                return data.data;
            } else {
                // Token invalid, logout
                this.logout();
                return null;
            }
        } catch (error) {
            console.error('Get current user error:', error);
            return null;
        }
    }
};

