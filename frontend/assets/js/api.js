// API Client with automatic authentication
const API = {
    BASE_URL: 'http://localhost/library-management-system-2025/backend/index.php/api',
    
    // Get auth headers
    getHeaders(includeAuth = true) {
        const headers = {
            'Content-Type': 'application/json'
        };
        
        if (includeAuth && AuthService.isAuthenticated()) {
            headers['Authorization'] = `Bearer ${AuthService.getToken()}`;
        }
        
        return headers;
    },
    
    // Handle API response
    async handleResponse(response) {
        const data = await response.json();
        
        // If unauthorized, logout and redirect
        if (response.status === 401) {
            AuthService.logout();
            return { success: false, message: 'Session expired. Please login again.', unauthorized: true };
        }
        
        return data;
    },
    
    // GET request
    async get(endpoint, requireAuth = true) {
        try {
            const response = await fetch(`${this.BASE_URL}${endpoint}`, {
                method: 'GET',
                headers: this.getHeaders(requireAuth)
            });
            return await this.handleResponse(response);
        } catch (error) {
            console.error('GET error:', error);
            return { success: false, message: 'Network error' };
        }
    },
    
    // POST request
    async post(endpoint, data, requireAuth = true) {
        try {
            const response = await fetch(`${this.BASE_URL}${endpoint}`, {
                method: 'POST',
                headers: this.getHeaders(requireAuth),
                body: JSON.stringify(data)
            });
            return await this.handleResponse(response);
        } catch (error) {
            console.error('POST error:', error);
            return { success: false, message: 'Network error' };
        }
    },
    
    // PUT request
    async put(endpoint, data, requireAuth = true) {
        try {
            const response = await fetch(`${this.BASE_URL}${endpoint}`, {
                method: 'PUT',
                headers: this.getHeaders(requireAuth),
                body: JSON.stringify(data)
            });
            return await this.handleResponse(response);
        } catch (error) {
            console.error('PUT error:', error);
            return { success: false, message: 'Network error' };
        }
    },
    
    // DELETE request
    async delete(endpoint, requireAuth = true) {
        try {
            const response = await fetch(`${this.BASE_URL}${endpoint}`, {
                method: 'DELETE',
                headers: this.getHeaders(requireAuth)
            });
            return await this.handleResponse(response);
        } catch (error) {
            console.error('DELETE error:', error);
            return { success: false, message: 'Network error' };
        }
    },
    
    // Specific API methods
    // Books
    getBooks() { return this.get('/books', false); },
    getBook(id) { return this.get(`/books/${id}`, false); },
    createBook(data) { return this.post('/books', data); },
    updateBook(id, data) { return this.put(`/books/${id}`, data); },
    deleteBook(id) { return this.delete(`/books/${id}`); },
    
    // Authors
    getAuthors() { return this.get('/authors', false); },
    getAuthor(id) { return this.get(`/authors/${id}`, false); },
    createAuthor(data) { return this.post('/authors', data); },
    updateAuthor(id, data) { return this.put(`/authors/${id}`, data); },
    deleteAuthor(id) { return this.delete(`/authors/${id}`); },
    
    // Genres
    getGenres() { return this.get('/genres', false); },
    getGenre(id) { return this.get(`/genres/${id}`, false); },
    createGenre(data) { return this.post('/genres', data); },
    updateGenre(id, data) { return this.put(`/genres/${id}`, data); },
    deleteGenre(id) { return this.delete(`/genres/${id}`); },
    
    // Users
    getUsers() { return this.get('/users'); },
    getUser(id) { return this.get(`/users/${id}`); },
    createUser(data) { return this.post('/users', data); },
    updateUser(id, data) { return this.put(`/users/${id}`, data); },
    deleteUser(id) { return this.delete(`/users/${id}`); },
    
    // Borrowings
    getBorrowings() { return this.get('/borrowings'); },
    getBorrowing(id) { return this.get(`/borrowings/${id}`); },
    createBorrowing(data) { return this.post('/borrowings', data); },
    updateBorrowing(id, data) { return this.put(`/borrowings/${id}`, data); },
    deleteBorrowing(id) { return this.delete(`/borrowings/${id}`); },
    returnBook(id) { return this.post(`/borrowings/${id}/return`); },
    getBorrowingsByUser(userId) { return this.get(`/borrowings/user/${userId}`); },
    getActiveBorrowings() { return this.get('/borrowings/active'); }
};

