import adminState from './shared-state.js';

class ApiService {
    constructor() {
        this.baseUrl = '/parliament-library/admin/api';
        this.headers = {
            'Content-Type': 'application/json'
        };
    }

    // Set authentication token
    setAuthToken(token) {
        this.headers['Authorization'] = `Bearer ${token}`;
    }

    // Clear authentication token
    clearAuthToken() {
        delete this.headers['Authorization'];
    }

    // Generic request handler
    async request(endpoint, options = {}) {
        try {
            adminState.setLoading(true);
            adminState.clearError();

            const response = await fetch(`${this.baseUrl}${endpoint}`, {
                ...options,
                headers: {
                    ...this.headers,
                    ...options.headers
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            return data;
        } catch (error) {
            adminState.setError(error.message);
            throw error;
        } finally {
            adminState.setLoading(false);
        }
    }

    // Authentication
    async login(credentials) {
        const data = await this.request('/auth.php', {
            method: 'POST',
            body: JSON.stringify({
                action: 'login',
                username: credentials.username,
                password: credentials.password
            })
        });
        if (data.token) this.setAuthToken(data.token);
        if (data.user) adminState.setUser(data.user);
        return data;
    }

    async logout() {
        await this.request('/auth/logout', { method: 'POST' });
        this.clearAuthToken();
        adminState.setUser(null);
    }

    // Books
    async getBooks(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const data = await this.request(`/books?${queryString}`);
        adminState.setState({ books: data.books });
        return data;
    }

    async getBook(id) {
        const data = await this.request(`/books/${id}`);
        return data;
    }

    async createBook(bookData) {
        const data = await this.request('/books', {
            method: 'POST',
            body: JSON.stringify(bookData)
        });
        adminState.setState({
            books: [...adminState.getState().books, data.book]
        });
        return data;
    }

    async updateBook(id, bookData) {
        const data = await this.request(`/books/${id}`, {
            method: 'PUT',
            body: JSON.stringify(bookData)
        });
        adminState.setState({
            books: adminState.getState().books.map(book => 
                book.id === id ? data.book : book
            )
        });
        return data;
    }

    async deleteBook(id) {
        await this.request(`/books/${id}`, { method: 'DELETE' });
        adminState.setState({
            books: adminState.getState().books.filter(book => book.id !== id)
        });
    }

    // Members
    async getMembers(params = {}) {
        try {
            const queryString = new URLSearchParams(params).toString();
            const response = await fetch(`${this.baseUrl}/members?${queryString}`);
            if (!response.ok) throw new Error('Failed to fetch members');
            return await response.json();
        } catch (error) {
            console.error('Error fetching members:', error);
            return { members: [], total: 0 };
        }
    }

    async getMember(id) {
        const data = await this.request(`/members/${id}`);
        return data;
    }

    async createMember(memberData) {
        const data = await this.request('/members', {
            method: 'POST',
            body: JSON.stringify(memberData)
        });
        adminState.setState({
            members: [...adminState.getState().members, data.member]
        });
        return data;
    }

    async updateMember(id, memberData) {
        const data = await this.request(`/members/${id}`, {
            method: 'PUT',
            body: JSON.stringify(memberData)
        });
        adminState.setState({
            members: adminState.getState().members.map(member => 
                member.id === id ? data.member : member
            )
        });
        return data;
    }

    async deleteMember(id) {
        await this.request(`/members/${id}`, { method: 'DELETE' });
        adminState.setState({
            members: adminState.getState().members.filter(member => member.id !== id)
        });
    }

    // Categories
    async getCategories() {
        try {
            const response = await fetch(`${this.baseUrl}/categories`);
            if (!response.ok) throw new Error('Failed to fetch categories');
            return await response.json();
        } catch (error) {
            console.error('Error fetching categories:', error);
            return [];
        }
    }

    async createCategory(categoryData) {
        const data = await this.request('/categories', {
            method: 'POST',
            body: JSON.stringify(categoryData)
        });
        adminState.setState({
            categories: [...adminState.getState().categories, data.category]
        });
        return data;
    }

    // Circulation
    async checkoutBook(bookId, memberId) {
        const data = await this.request('/circulation/checkout', {
            method: 'POST',
            body: JSON.stringify({ bookId, memberId })
        });
        return data;
    }

    async returnBook(bookId, memberId) {
        const data = await this.request('/circulation/return', {
            method: 'POST',
            body: JSON.stringify({ bookId, memberId })
        });
        return data;
    }

    // Reports
    async getReports(type, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const data = await this.request(`/reports/${type}?${queryString}`);
        return data;
    }

    async getStats() {
        try {
            const response = await fetch(`${this.baseUrl}/stats`);
            if (!response.ok) throw new Error('Failed to fetch stats');
            return await response.json();
        } catch (error) {
            console.error('Error fetching stats:', error);
            return {
                activeMembers: 0,
                checkedOutItems: 0,
                overdueItems: 0,
                newItems: 0
            };
        }
    }

    async getRecentActivity(limit = 5) {
        try {
            const response = await fetch(`${this.baseUrl}/activity?limit=${limit}`);
            if (!response.ok) throw new Error('Failed to fetch recent activity');
            return await response.json();
        } catch (error) {
            console.error('Error fetching recent activity:', error);
            return [];
        }
    }

    async getCirculationStats() {
        try {
            const response = await fetch(`${this.baseUrl}/circulation/stats`);
            if (!response.ok) throw new Error('Failed to fetch circulation stats');
            return await response.json();
        } catch (error) {
            console.error('Error fetching circulation stats:', error);
            return {
                totalCheckouts: 0,
                totalReturns: 0,
                activeLoans: 0,
                overdueItems: 0
            };
        }
    }

    async getReports() {
        try {
            const response = await fetch(`${this.baseUrl}/reports`);
            if (!response.ok) throw new Error('Failed to fetch reports');
            return await response.json();
        } catch (error) {
            console.error('Error fetching reports:', error);
            return [];
        }
    }
}

// Create singleton instance
const apiService = new ApiService();
export default apiService; 