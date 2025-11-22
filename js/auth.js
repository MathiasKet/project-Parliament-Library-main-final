// Simple auth utility for local development
const Auth = {
    // Check if user is authenticated
    isAuthenticated() {
        return sessionStorage.getItem('isAuthenticated') === 'true';
    },

    // Check if user has required role
    hasRole(role) {
        const user = JSON.parse(sessionStorage.getItem('user') || '{}');
        return user.role === role;
    },

    // Get current user
    getCurrentUser() {
        return JSON.parse(sessionStorage.getItem('user') || '{}');
    },

    // Logout user
    logout() {
        sessionStorage.removeItem('isAuthenticated');
        sessionStorage.removeItem('user');
    }
};

export default Auth;
    // Handle protected routes
    requireAuth() {
        if (!this.isAuthenticated()) {
            window.location.href = 'login.html';
            return false;
        }
        return true;
    }

    // Handle role-based access
    requireRole(role) {
        if (!this.hasRole(role)) {
            window.location.href = 'unauthorized.html';
            return false;
        }
        return true;
    }
}

// Create singleton instance
const auth = new Auth();
export default auth; 