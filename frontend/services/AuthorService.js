// Author Service - Handles all author-related operations
const AuthorService = {
    // Get all authors
    async getAll() {
        try {
            const result = await API.getAuthors();
            return result;
        } catch (error) {
            console.error('Error fetching authors:', error);
            return { success: false, message: 'Failed to fetch authors' };
        }
    },

    // Get a single author by ID
    async getById(id) {
        try {
            const result = await API.getAuthor(id);
            return result;
        } catch (error) {
            console.error('Error fetching author:', error);
            return { success: false, message: 'Failed to fetch author' };
        }
    },

    // Create a new author
    async create(authorData) {
        const validation = this.validateAuthor(authorData);
        if (!validation.valid) {
            return { success: false, message: validation.message };
        }

        try {
            const result = await API.createAuthor(authorData);
            if (result.success) {
                toastr.success('Author created successfully!');
            }
            return result;
        } catch (error) {
            console.error('Error creating author:', error);
            return { success: false, message: 'Failed to create author' };
        }
    },

    // Update an existing author
    async update(id, authorData) {
        const validation = this.validateAuthor(authorData);
        if (!validation.valid) {
            return { success: false, message: validation.message };
        }

        try {
            const result = await API.updateAuthor(id, authorData);
            if (result.success) {
                toastr.success('Author updated successfully!');
            }
            return result;
        } catch (error) {
            console.error('Error updating author:', error);
            return { success: false, message: 'Failed to update author' };
        }
    },

    // Delete an author
    async delete(id) {
        if (!confirm('Are you sure you want to delete this author?')) {
            return { success: false, message: 'Deletion cancelled' };
        }

        try {
            const result = await API.deleteAuthor(id);
            if (result.success) {
                toastr.success('Author deleted successfully!');
            }
            return result;
        } catch (error) {
            console.error('Error deleting author:', error);
            return { success: false, message: 'Failed to delete author' };
        }
    },

    // Validate author data
    validateAuthor(authorData) {
        if (!authorData.name || authorData.name.trim().length < 2) {
            return { valid: false, message: 'Name is required and must be at least 2 characters' };
        }
        if (authorData.biography && authorData.biography.length > 1000) {
            return { valid: false, message: 'Biography must be 1000 characters or less' };
        }
        if (authorData.nationality && authorData.nationality.length > 50) {
            return { valid: false, message: 'Nationality must be 50 characters or less' };
        }
        return { valid: true };
    },

    // Initialize authors view
    async initAuthorsView() {
        if (!AuthService.isAuthenticated()) {
            window.location.hash = '#/login';
            return;
        }

        await this.loadAuthors();
        this.setupAuthorForm();
    },

    // Load and display authors
    async loadAuthors() {
        const result = await this.getAll();
        const authorsTableBody = document.getElementById('authorsTableBody');

        if (!authorsTableBody) return;

        if (result.success && result.data) {
            const authors = Array.isArray(result.data) ? result.data : [];
            if (authors.length === 0) {
                authorsTableBody.innerHTML = '<tr><td colspan="4" class="text-center">No authors found</td></tr>';
                return;
            }

            authorsTableBody.innerHTML = authors.map(author => `
                <tr>
                    <td>${author.id || ''}</td>
                    <td>${this.escapeHtml(author.name || '')}</td>
                    <td>${this.escapeHtml(author.nationality || 'N/A')}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="AuthorService.editAuthor(${author.id})">Edit</button>
                        ${AuthService.isAdmin() ? `<button class="btn btn-sm btn-danger" onclick="AuthorService.deleteAuthor(${author.id})">Delete</button>` : ''}
                    </td>
                </tr>
            `).join('');
        } else {
            authorsTableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading authors</td></tr>';
            if (result.message) toastr.error(result.message);
        }
    },

    // Setup author form
    setupAuthorForm() {
        const form = document.getElementById('authorForm');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const authorData = {
                name: formData.get('name'),
                nationality: formData.get('nationality') || null,
                biography: formData.get('biography') || null
            };

            const authorId = form.dataset.authorId;
            if (authorId) {
                await this.update(authorId, authorData);
            } else {
                await this.create(authorData);
            }

            form.reset();
            form.dataset.authorId = '';
            document.getElementById('authorFormTitle').textContent = 'Add New Author';
            await this.loadAuthors();
        });
    },

    // Edit author
    async editAuthor(id) {
        const result = await this.getById(id);
        if (result.success && result.data) {
            const author = result.data;
            const form = document.getElementById('authorForm');
            if (form) {
                form.dataset.authorId = id;
                document.getElementById('authorName').value = author.name || '';
                document.getElementById('authorNationality').value = author.nationality || '';
                document.getElementById('authorBiography').value = author.biography || '';
                document.getElementById('authorFormTitle').textContent = 'Edit Author';
                form.scrollIntoView({ behavior: 'smooth' });
            }
        } else {
            toastr.error(result.message || 'Failed to load author');
        }
    },

    // Delete author
    async deleteAuthor(id) {
        const result = await this.delete(id);
        if (result.success) {
            await this.loadAuthors();
        } else {
            toastr.error(result.message || 'Failed to delete author');
        }
    },

    // Escape HTML to prevent XSS
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

