class AdminState {
    constructor() {
        this.state = {
            currentPage: 'dashboard',
            user: null,
            notifications: [],
            books: [],
            members: [],
            categories: [],
            isLoading: false,
            error: null
        };
        this.subscribers = new Map();
    }

    // Get current state
    getState() {
        return { ...this.state };
    }

    // Update state
    setState(newState) {
        this.state = { ...this.state, ...newState };
        this.notifySubscribers();
    }

    // Subscribe to state changes
    subscribe(key, callback) {
        if (!this.subscribers.has(key)) {
            this.subscribers.set(key, new Set());
        }
        this.subscribers.get(key).add(callback);
    }

    // Unsubscribe from state changes
    unsubscribe(key, callback) {
        if (this.subscribers.has(key)) {
            this.subscribers.get(key).delete(callback);
        }
    }

    // Notify subscribers of state changes
    notifySubscribers() {
        this.subscribers.forEach((callbacks, key) => {
            callbacks.forEach(callback => callback(this.state));
        });
    }

    // Specific state updates
    setUser(user) {
        this.setState({ user });
    }

    addNotification(notification) {
        const notifications = [...this.state.notifications, notification];
        this.setState({ notifications });
    }

    removeNotification(id) {
        const notifications = this.state.notifications.filter(n => n.id !== id);
        this.setState({ notifications });
    }

    setLoading(isLoading) {
        this.setState({ isLoading });
    }

    setError(error) {
        this.setState({ error });
    }

    clearError() {
        this.setState({ error: null });
    }
}

// Create singleton instance
const adminState = new AdminState();
export default adminState; 