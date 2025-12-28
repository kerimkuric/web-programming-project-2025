// Authentication Service
const AuthService = {
    TOKEN_KEY: 'lms_token',
    USER_KEY: 'lms_user',

    getToken() {
        return localStorage.getItem(this.TOKEN_KEY);
    },

    getUser() {
        const userStr = localStorage.getItem(this.USER_KEY);
        return userStr ? JSON.parse(userStr) : null;
    },

    isAuthenticated() {
        return !!this.getToken();
    },

    isAdmin() {
        const user = this.getUser();
        if (!user) return false;
        return user.is_admin === 1 || user.is_admin === true || user.role === 'admin';
    },

    async login(email, password) {
        try {
            const res = await fetch(Constants.PROJECT_BASE_URL + 'auth/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });

            const data = await res.json();
            if (data.success) {
                localStorage.setItem(this.TOKEN_KEY, data.data.token);
                localStorage.setItem(this.USER_KEY, JSON.stringify(data.data));
                return { success: true, user: data.data };
            }
            return { success: false, message: data.message || data.error || 'Login failed' };
        } catch (err) {
            console.error('Login error:', err);
            return { success: false, message: 'Network error' };
        }
    },

    async register(userData) {
        try {
            const res = await fetch(Constants.PROJECT_BASE_URL + 'auth/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(userData)
            });

            const data = await res.json();
            if (data.success) return { success: true, message: 'Registration successful' };
            return { success: false, message: data.message || data.error || 'Registration failed' };
        } catch (err) {
            console.error('Register error:', err);
            return { success: false, message: 'Network error' };
        }
    },

    logout() {
        localStorage.removeItem(this.TOKEN_KEY);
        localStorage.removeItem(this.USER_KEY);
        window.location.hash = '#/login';
    },

    async getCurrentUser() {
        try {
            const token = this.getToken();
            if (!token) return null;

            const res = await fetch(Constants.PROJECT_BASE_URL + 'auth/me', {
                headers: { 'Authorization': `Bearer ${token}` }
            });

            const data = await res.json();
            if (data.success) {
                localStorage.setItem(this.USER_KEY, JSON.stringify(data.data));
                return data.data;
            }
            this.logout();
            return null;
        } catch (err) {
            console.error('Get current user error:', err);
            return null;
        }
    },

    // Initialize login/register forms (replaces AuthController)
    init() {
        // Initialize login form
        if ($("#login-form").length) {
            this.initLoginForm();
        }

        // Initialize register form
        if ($("#register-form").length) {
            this.initRegisterForm();
        }
    },

    initLoginForm() {
        $("#login-form").validate({
            rules: {
                email: {
                    required: true,
                    email: true
                },
                password: {
                    required: true,
                    minlength: 6
                }
            },
            messages: {
                email: {
                    required: "Email is required",
                    email: "Please enter a valid email address"
                },
                password: {
                    required: "Password is required",
                    minlength: "Password must be at least 6 characters"
                }
            },
            submitHandler: (form) => {
                const entity = Object.fromEntries(new FormData(form).entries());
                this.handleLogin(entity);
            }
        });
    },

    initRegisterForm() {
        $("#register-form").validate({
            rules: {
                name: {
                    required: true,
                    minlength: 2
                },
                phone: {
                    required: true,
                    pattern: /^[\+]?[0-9\-\(\)\s]+$/
                },
                email: {
                    required: true,
                    email: true
                },
                password: {
                    required: true,
                    minlength: 6
                },
                password_confirm: {
                    required: true,
                    equalTo: "#regPassword"
                }
            },
            messages: {
                name: {
                    required: "Name is required",
                    minlength: "Name must be at least 2 characters"
                },
                phone: {
                    required: "Phone number is required",
                    pattern: "Please enter a valid phone number"
                },
                email: {
                    required: "Email is required",
                    email: "Please enter a valid email address"
                },
                password: {
                    required: "Password is required",
                    minlength: "Password must be at least 6 characters"
                },
                password_confirm: {
                    required: "Please confirm your password",
                    equalTo: "Passwords do not match"
                }
            },
            submitHandler: (form) => {
                const entity = Object.fromEntries(new FormData(form).entries());
                // Remove password_confirm before sending to API
                delete entity.password_confirm;
                this.handleRegister(entity);
            }
        });
    },

    async handleLogin(entity) {
        const result = await this.login(entity.email, entity.password);
        if (result.success) {
            toastr.success("Login successful!");
            window.location.hash = '#/dashboard';
        } else {
            toastr.error(result.message || "Login failed");
        }
    },

    async handleRegister(entity) {
        const result = await this.register(entity);
        if (result.success) {
            toastr.success("Registration successful! Please login.");
            setTimeout(() => {
                window.location.hash = '#/login';
            }, 1500);
        } else {
            toastr.error(result.message || "Registration failed");
        }
    }
};
