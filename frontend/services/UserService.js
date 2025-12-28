// User Service - Handles all user-related operations (non-auth)
const UserService = {
    // Get all users (admin only)
    async getAll() {
        if (!AuthService.isAdmin()) {
            return { success: false, message: 'Unauthorized' };
        }
        try {
            const result = await API.getUsers();
            return result;
        } catch (error) {
            console.error('Error fetching users:', error);
            return { success: false, message: 'Failed to fetch users' };
        }
    },

    // Get a single user by ID
    async getById(id) {
        try {
            const result = await API.getUser(id);
            return result;
        } catch (error) {
            console.error('Error fetching user:', error);
            return { success: false, message: 'Failed to fetch user' };
        }
    },

    // Create a new user (admin only)
    async create(userData) {
        if (!AuthService.isAdmin()) {
            return { success: false, message: 'Unauthorized' };
        }

        const validation = this.validateUser(userData);
        if (!validation.valid) {
            return { success: false, message: validation.message };
        }

        try {
            const result = await API.createUser(userData);
            if (result.success) {
                toastr.success('User created successfully!');
            }
            return result;
        } catch (error) {
            console.error('Error creating user:', error);
            return { success: false, message: 'Failed to create user' };
        }
    },

    // Update an existing user
    async update(id, userData) {
        const validation = this.validateUser(userData, true);
        if (!validation.valid) {
            return { success: false, message: validation.message };
        }

        try {
            const result = await API.updateUser(id, userData);
            if (result.success) {
                toastr.success('User updated successfully!');
            }
            return result;
        } catch (error) {
            console.error('Error updating user:', error);
            return { success: false, message: 'Failed to update user' };
        }
    },

    // Delete a user (admin only)
    async delete(id) {
        if (!AuthService.isAdmin()) {
            return { success: false, message: 'Unauthorized' };
        }

        if (!confirm('Are you sure you want to delete this user?')) {
            return { success: false, message: 'Deletion cancelled' };
        }

        try {
            const result = await API.deleteUser(id);
            if (result.success) {
                toastr.success('User deleted successfully!');
            }
            return result;
        } catch (error) {
            console.error('Error deleting user:', error);
            return { success: false, message: 'Failed to delete user' };
        }
    },

    // Validate user data
    validateUser(userData, isUpdate = false) {
        if (!isUpdate && (!userData.name || userData.name.trim().length < 2)) {
            return { valid: false, message: 'Name is required and must be at least 2 characters' };
        }
        if (!isUpdate && (!userData.email || !this.isValidEmail(userData.email))) {
            return { valid: false, message: 'Valid email is required' };
        }
        if (!isUpdate && (!userData.password || userData.password.length < 6)) {
            return { valid: false, message: 'Password is required and must be at least 6 characters' };
        }
        if (userData.phone && !/^[\+]?[0-9\-\(\)\s]+$/.test(userData.phone)) {
            return { valid: false, message: 'Please enter a valid phone number' };
        }
        return { valid: true };
    },

    // Email validation
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },

    // Initialize users view
    async initUsersView() {
        if (!AuthService.isAuthenticated()) {
            window.location.hash = '#/login';
            return;
        }

        if (!AuthService.isAdmin()) {
            window.location.hash = '#/dashboard';
            toastr.error('Unauthorized access');
            return;
        }

        await this.loadUsers();
        this.setupUserForm();
    },

    // Load and display users
    async loadUsers() {
        const result = await this.getAll();
        const usersTableBody = document.getElementById('usersTableBody');

        if (!usersTableBody) return;

        if (result.success && result.data) {
            const users = Array.isArray(result.data) ? result.data : [];
            if (users.length === 0) {
                usersTableBody.innerHTML = '<tr><td colspan="5" class="text-center">No users found</td></tr>';
                return;
            }

            usersTableBody.innerHTML = users.map(user => `
                <tr>
                    <td>${user.id || ''}</td>
                    <td>${this.escapeHtml(user.name || '')}</td>
                    <td>${this.escapeHtml(user.email || '')}</td>
                    <td>${this.escapeHtml(user.phone || 'N/A')}</td>
                    <td><span class="badge ${user.role === Constants.ADMIN_ROLE ? 'bg-danger' : 'bg-secondary'}">${user.role || 'user'}</span></td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="UserService.editUser(${user.id})">Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="UserService.deleteUser(${user.id})">Delete</button>
                    </td>
                </tr>
            `).join('');
        } else {
            usersTableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading users</td></tr>';
            if (result.message) toastr.error(result.message);
        }
    },

    // Setup user form
    setupUserForm() {
        const form = document.getElementById('userForm');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const userData = {
                name: formData.get('name'),
                email: formData.get('email'),
                phone: formData.get('phone') || null,
                password: formData.get('password') || null,
                role: formData.get('role') || Constants.USER_ROLE
            };

            // Remove password if empty (for updates)
            if (!userData.password) {
                delete userData.password;
            }

            const userId = form.dataset.userId;
            if (userId) {
                await this.update(userId, userData);
            } else {
                await this.create(userData);
            }

            form.reset();
            form.dataset.userId = '';
            document.getElementById('userFormTitle').textContent = 'Add New User';
            await this.loadUsers();
        });
    },

    // Edit user
    async editUser(id) {
        const result = await this.getById(id);
        if (result.success && result.data) {
            const user = result.data;
            const form = document.getElementById('userForm');
            if (form) {
                form.dataset.userId = id;
                document.getElementById('userName').value = user.name || '';
                document.getElementById('userEmail').value = user.email || '';
                document.getElementById('userPhone').value = user.phone || '';
                document.getElementById('userRole').value = user.role || Constants.USER_ROLE;
                document.getElementById('userFormTitle').textContent = 'Edit User';
                form.scrollIntoView({ behavior: 'smooth' });
            }
        } else {
            toastr.error(result.message || 'Failed to load user');
        }
    },

    // Delete user
    async deleteUser(id) {
        const result = await this.delete(id);
        if (result.success) {
            await this.loadUsers();
        } else {
            toastr.error(result.message || 'Failed to delete user');
        }
    },

    // Initialize profile view
    async initProfileView() {
        if (!AuthService.isAuthenticated()) {
            window.location.hash = '#/login';
            return;
        }

        const user = AuthService.getUser();
        if (user) {
            this.loadProfile(user);
            this.setupProfileForm();
        }
    },

    // Load profile data
    loadProfile(user) {
        const nameField = document.getElementById('profileName');
        const emailField = document.getElementById('profileEmail');
        const phoneField = document.getElementById('profilePhone');
        const roleField = document.getElementById('profileRole');

        if (nameField) nameField.value = user.name || '';
        if (emailField) emailField.value = user.email || '';
        if (phoneField) phoneField.value = user.phone || '';
        if (roleField) roleField.textContent = user.role || 'user';
    },

    // Setup profile form
    setupProfileForm() {
        const form = document.getElementById('profileForm');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const user = AuthService.getUser();
            const userData = {
                name: formData.get('name'),
                phone: formData.get('phone') || null
            };

            const result = await this.update(user.id, userData);
            if (result.success) {
                // Update stored user data
                const updatedUser = { ...user, ...userData };
                localStorage.setItem('lms_user', JSON.stringify(updatedUser));
                toastr.success('Profile updated successfully!');
            }
        });
    },

    // Escape HTML to prevent XSS
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

