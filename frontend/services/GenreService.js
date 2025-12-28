// Genre Service - Handles all genre-related operations
const GenreService = {
    // Get all genres
    async getAll() {
        try {
            const result = await API.getGenres();
            return result;
        } catch (error) {
            console.error('Error fetching genres:', error);
            return { success: false, message: 'Failed to fetch genres' };
        }
    },

    // Get a single genre by ID
    async getById(id) {
        try {
            const result = await API.getGenre(id);
            return result;
        } catch (error) {
            console.error('Error fetching genre:', error);
            return { success: false, message: 'Failed to fetch genre' };
        }
    },

    // Create a new genre
    async create(genreData) {
        const validation = this.validateGenre(genreData);
        if (!validation.valid) {
            return { success: false, message: validation.message };
        }

        try {
            const result = await API.createGenre(genreData);
            if (result.success) {
                toastr.success('Genre created successfully!');
            }
            return result;
        } catch (error) {
            console.error('Error creating genre:', error);
            return { success: false, message: 'Failed to create genre' };
        }
    },

    // Update an existing genre
    async update(id, genreData) {
        const validation = this.validateGenre(genreData);
        if (!validation.valid) {
            return { success: false, message: validation.message };
        }

        try {
            const result = await API.updateGenre(id, genreData);
            if (result.success) {
                toastr.success('Genre updated successfully!');
            }
            return result;
        } catch (error) {
            console.error('Error updating genre:', error);
            return { success: false, message: 'Failed to update genre' };
        }
    },

    // Delete a genre
    async delete(id) {
        if (!confirm('Are you sure you want to delete this genre?')) {
            return { success: false, message: 'Deletion cancelled' };
        }

        try {
            const result = await API.deleteGenre(id);
            if (result.success) {
                toastr.success('Genre deleted successfully!');
            }
            return result;
        } catch (error) {
            console.error('Error deleting genre:', error);
            return { success: false, message: 'Failed to delete genre' };
        }
    },

    // Validate genre data
    validateGenre(genreData) {
        if (!genreData.name || genreData.name.trim().length < 2) {
            return { valid: false, message: 'Name is required and must be at least 2 characters' };
        }
        if (genreData.description && genreData.description.length > 500) {
            return { valid: false, message: 'Description must be 500 characters or less' };
        }
        return { valid: true };
    },

    // Initialize genres view
    async initGenresView() {
        if (!AuthService.isAuthenticated()) {
            window.location.hash = '#/login';
            return;
        }

        await this.loadGenres();
        this.setupGenreForm();
    },

    // Load and display genres
    async loadGenres() {
        const result = await this.getAll();
        const genresTableBody = document.getElementById('genresTableBody');

        if (!genresTableBody) return;

        if (result.success && result.data) {
            const genres = Array.isArray(result.data) ? result.data : [];
            if (genres.length === 0) {
                genresTableBody.innerHTML = '<tr><td colspan="3" class="text-center">No genres found</td></tr>';
                return;
            }

            genresTableBody.innerHTML = genres.map(genre => `
                <tr>
                    <td>${genre.id || ''}</td>
                    <td>${this.escapeHtml(genre.name || '')}</td>
                    <td>${this.escapeHtml(genre.description || 'N/A')}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="GenreService.editGenre(${genre.id})">Edit</button>
                        ${AuthService.isAdmin() ? `<button class="btn btn-sm btn-danger" onclick="GenreService.deleteGenre(${genre.id})">Delete</button>` : ''}
                    </td>
                </tr>
            `).join('');
        } else {
            genresTableBody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Error loading genres</td></tr>';
            if (result.message) toastr.error(result.message);
        }
    },

    // Setup genre form
    setupGenreForm() {
        const form = document.getElementById('genreForm');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const genreData = {
                name: formData.get('name'),
                description: formData.get('description') || null
            };

            const genreId = form.dataset.genreId;
            if (genreId) {
                await this.update(genreId, genreData);
            } else {
                await this.create(genreData);
            }

            form.reset();
            form.dataset.genreId = '';
            document.getElementById('genreFormTitle').textContent = 'Add New Genre';
            await this.loadGenres();
        });
    },

    // Edit genre
    async editGenre(id) {
        const result = await this.getById(id);
        if (result.success && result.data) {
            const genre = result.data;
            const form = document.getElementById('genreForm');
            if (form) {
                form.dataset.genreId = id;
                document.getElementById('genreName').value = genre.name || '';
                document.getElementById('genreDescription').value = genre.description || '';
                document.getElementById('genreFormTitle').textContent = 'Edit Genre';
                form.scrollIntoView({ behavior: 'smooth' });
            }
        } else {
            toastr.error(result.message || 'Failed to load genre');
        }
    },

    // Delete genre
    async deleteGenre(id) {
        const result = await this.delete(id);
        if (result.success) {
            await this.loadGenres();
        } else {
            toastr.error(result.message || 'Failed to delete genre');
        }
    },

    // Escape HTML to prevent XSS
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

