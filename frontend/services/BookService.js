// Book Service - Handles all book-related operations
const BookService = {
    // Get all books
    async getAll() {
        try {
            const result = await API.getBooks();
            return result;
        } catch (error) {
            console.error('Error fetching books:', error);
            return { success: false, message: 'Failed to fetch books' };
        }
    },

    // Get a single book by ID
    async getById(id) {
        try {
            const result = await API.getBook(id);
            return result;
        } catch (error) {
            console.error('Error fetching book:', error);
            return { success: false, message: 'Failed to fetch book' };
        }
    },

    // Create a new book
    async create(bookData) {
        // Client-side validation
        const validation = this.validateBook(bookData);
        if (!validation.valid) {
            return { success: false, message: validation.message };
        }

        try {
            const result = await API.createBook(bookData);
            if (result.success) {
                toastr.success('Book created successfully!');
            }
            return result;
        } catch (error) {
            console.error('Error creating book:', error);
            return { success: false, message: 'Failed to create book' };
        }
    },

    // Update an existing book
    async update(id, bookData) {
        // Client-side validation
        const validation = this.validateBook(bookData);
        if (!validation.valid) {
            return { success: false, message: validation.message };
        }

        try {
            const result = await API.updateBook(id, bookData);
            if (result.success) {
                toastr.success('Book updated successfully!');
            }
            return result;
        } catch (error) {
            console.error('Error updating book:', error);
            return { success: false, message: 'Failed to update book' };
        }
    },

    // Delete a book
    async delete(id) {
        if (!confirm('Are you sure you want to delete this book?')) {
            return { success: false, message: 'Deletion cancelled' };
        }

        try {
            const result = await API.deleteBook(id);
            if (result.success) {
                toastr.success('Book deleted successfully!');
            }
            return result;
        } catch (error) {
            console.error('Error deleting book:', error);
            return { success: false, message: 'Failed to delete book' };
        }
    },

    // Validate book data
    validateBook(bookData) {
        if (!bookData.title || bookData.title.trim().length < 1) {
            return { valid: false, message: 'Title is required and must be at least 1 character' };
        }
        if (!bookData.author_id || isNaN(bookData.author_id)) {
            return { valid: false, message: 'Valid author is required' };
        }
        if (!bookData.genre_id || isNaN(bookData.genre_id)) {
            return { valid: false, message: 'Valid genre is required' };
        }
        if (bookData.isbn && bookData.isbn.length > 20) {
            return { valid: false, message: 'ISBN must be 20 characters or less' };
        }
        if (bookData.publication_year && (isNaN(bookData.publication_year) || bookData.publication_year < 1000 || bookData.publication_year > new Date().getFullYear())) {
            return { valid: false, message: 'Publication year must be a valid year' };
        }
        return { valid: true };
    },

    // Initialize books view
    async initBooksView() {
        if (!AuthService.isAuthenticated()) {
            window.location.hash = '#/login';
            return;
        }

        this.loadBooks();
        this.setupBookForm();
    },

    // Load and display books
    async loadBooks() {
        const result = await this.getAll();
        const booksTable = document.getElementById('booksTable');
        const booksTableBody = document.getElementById('booksTableBody');

        if (!booksTableBody) return;

        if (result.success && result.data) {
            const books = Array.isArray(result.data) ? result.data : [];
            if (books.length === 0) {
                booksTableBody.innerHTML = '<tr><td colspan="7" class="text-center">No books found</td></tr>';
                return;
            }

            booksTableBody.innerHTML = books.map(book => `
                <tr>
                    <td>${book.id || ''}</td>
                    <td>${this.escapeHtml(book.title || '')}</td>
                    <td>${this.escapeHtml(book.author_name || 'N/A')}</td>
                    <td>${this.escapeHtml(book.genre_name || 'N/A')}</td>
                    <td>${book.isbn || 'N/A'}</td>
                    <td>${book.publication_year || 'N/A'}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="BookService.editBook(${book.id})">Edit</button>
                        ${AuthService.isAdmin() ? `<button class="btn btn-sm btn-danger" onclick="BookService.deleteBook(${book.id})">Delete</button>` : ''}
                    </td>
                </tr>
            `).join('');
        } else {
            booksTableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading books</td></tr>';
            if (result.message) toastr.error(result.message);
        }
    },

    // Setup book form
    setupBookForm() {
        const form = document.getElementById('bookForm');
        if (!form) return;

        // Load authors and genres for dropdowns
        this.loadAuthorsAndGenres();

        // Setup search functionality
        const searchInput = document.getElementById('bookSearch');
        if (searchInput) {
            let allBooks = [];
            let allAuthors = [];
            let allGenres = [];

            // Load all data for search
            Promise.all([this.getAll(), API.getAuthors(), API.getGenres()]).then(([booksResult, authorsResult, genresResult]) => {
                if (booksResult.success) allBooks = booksResult.data || [];
                if (authorsResult.success) allAuthors = authorsResult.data || [];
                if (genresResult.success) allGenres = genresResult.data || [];
            });

            searchInput.addEventListener('input', (e) => {
                const searchTerm = e.target.value.toLowerCase();
                if (!searchTerm) {
                    this.loadBooks();
                    return;
                }

                Promise.all([this.getAll(), API.getAuthors(), API.getGenres()]).then(([booksResult, authorsResult, genresResult]) => {
                    const books = booksResult.success ? (booksResult.data || []) : [];
                    const authors = authorsResult.success ? (authorsResult.data || []) : [];
                    const genres = genresResult.success ? (genresResult.data || []) : [];

                    const filtered = books.filter(book => {
                        const author = authors.find(a => a.id === book.author_id);
                        const genre = genres.find(g => g.id === book.genre_id);
                        return (book.title && book.title.toLowerCase().includes(searchTerm)) ||
                               (author && author.name && author.name.toLowerCase().includes(searchTerm)) ||
                               (genre && genre.name && genre.name.toLowerCase().includes(searchTerm)) ||
                               (book.isbn && book.isbn.toLowerCase().includes(searchTerm));
                    });

                    const booksTableBody = document.getElementById('booksTableBody');
                    if (booksTableBody) {
                        if (filtered.length === 0) {
                            booksTableBody.innerHTML = '<tr><td colspan="7" class="text-center">No books found</td></tr>';
                        } else {
                            booksTableBody.innerHTML = filtered.map(book => {
                                const author = authors.find(a => a.id === book.author_id);
                                const genre = genres.find(g => g.id === book.genre_id);
                                return `
                                    <tr>
                                        <td>${book.id || ''}</td>
                                        <td>${this.escapeHtml(book.title || '')}</td>
                                        <td>${this.escapeHtml(author ? author.name : 'N/A')}</td>
                                        <td>${this.escapeHtml(genre ? genre.name : 'N/A')}</td>
                                        <td>${book.isbn || 'N/A'}</td>
                                        <td>${book.publication_year || 'N/A'}</td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="BookService.editBook(${book.id})">Edit</button>
                                            ${AuthService.isAdmin() ? `<button class="btn btn-sm btn-danger" onclick="BookService.deleteBook(${book.id})">Delete</button>` : ''}
                                        </td>
                                    </tr>
                                `;
                            }).join('');
                        }
                    }
                });
            });
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const bookData = {
                title: formData.get('title'),
                author_id: parseInt(formData.get('author_id')),
                genre_id: parseInt(formData.get('genre_id')),
                isbn: formData.get('isbn') || null,
                publication_year: formData.get('publication_year') ? parseInt(formData.get('publication_year')) : null
            };

            const bookId = form.dataset.bookId;
            if (bookId) {
                await this.update(bookId, bookData);
            } else {
                await this.create(bookData);
            }

            form.reset();
            form.dataset.bookId = '';
            document.getElementById('bookFormTitle').textContent = 'Add New Book';
            await this.loadBooks();
        });
    },

    // Load authors and genres for dropdowns
    async loadAuthorsAndGenres() {
        try {
            const [authorsResult, genresResult] = await Promise.all([
                API.getAuthors(),
                API.getGenres()
            ]);

            const authorSelect = document.getElementById('bookAuthor');
            const genreSelect = document.getElementById('bookGenre');

            if (authorSelect && authorsResult.success && authorsResult.data) {
                authorSelect.innerHTML = '<option value="">Select Author</option>' +
                    authorsResult.data.map(author => 
                        `<option value="${author.id}">${this.escapeHtml(author.name)}</option>`
                    ).join('');
            }

            if (genreSelect && genresResult.success && genresResult.data) {
                genreSelect.innerHTML = '<option value="">Select Genre</option>' +
                    genresResult.data.map(genre => 
                        `<option value="${genre.id}">${this.escapeHtml(genre.name)}</option>`
                    ).join('');
            }
        } catch (error) {
            console.error('Error loading authors/genres:', error);
        }
    },

    // Edit book
    async editBook(id) {
        const result = await this.getById(id);
        if (result.success && result.data) {
            const book = result.data;
            const form = document.getElementById('bookForm');
            if (form) {
                form.dataset.bookId = id;
                document.getElementById('bookTitle').value = book.title || '';
                document.getElementById('bookAuthor').value = book.author_id || '';
                document.getElementById('bookGenre').value = book.genre_id || '';
                document.getElementById('bookIsbn').value = book.isbn || '';
                document.getElementById('bookPublicationYear').value = book.publication_year || '';
                document.getElementById('bookFormTitle').textContent = 'Edit Book';
                form.scrollIntoView({ behavior: 'smooth' });
            }
        } else {
            toastr.error(result.message || 'Failed to load book');
        }
    },

    // Delete book
    async deleteBook(id) {
        const result = await this.delete(id);
        if (result.success) {
            await this.loadBooks();
        } else {
            toastr.error(result.message || 'Failed to delete book');
        }
    },

    // Escape HTML to prevent XSS
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

