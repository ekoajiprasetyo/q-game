import { API } from './constants.js';

/**
 * API helper for Q-Game
 */
class GameAPI {
    constructor() {
        this.baseUrl = API.BASE_URL;
    }

    /**
     * Fetch all topics
     */
    async getTopics() {
        try {
            const response = await fetch(API.TOPICS);
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error fetching topics:', error);
            return [];
        }
    }

    /**
     * Fetch materials for a specific topic
     */
    async getMaterials(topicId) {
        try {
            const response = await fetch(`${API.TOPICS}/${topicId}/materials`);
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error fetching materials:', error);
            return [];
        }
    }

    /**
     * Fetch questions by topic
     */
    async getQuestionsByTopic(topicId, limit = 10) {
        try {
            const response = await fetch(`${API.TOPICS}/${topicId}/questions?limit=${limit}`);
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error fetching questions:', error);
            return [];
        }
    }

    /**
     * Fetch random questions for a topic
     */
    async getRandomQuestions(topicId, count = 10) {
        try {
            const response = await fetch(`${API.QUESTIONS}/random?topic_id=${topicId}&count=${count}`);
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error fetching random questions:', error);
            return [];
        }
    }

    /**
     * Fetch questions by session PIN
     */
    async getQuestionsByPin(pin, count = 10) {
        try {
            const response = await fetch(`${API.QUESTIONS}/by-pin/${pin}?count=${count}`);
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error fetching questions by PIN:', error);
            return { success: false, questions: [] };
        }
    }

    /**
     * Start a new game session
     */
    async startGame(config) {
        try {
            const response = await fetch(API.GAME_START, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify(config),
            });
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error starting game:', error);
            return null;
        }
    }

    /**
     * Submit round result
     */
    async submitRound(sessionId, roundData) {
        try {
            const url = API.GAME_ROUND.replace(':id', sessionId);
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify(roundData),
            });
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error submitting round:', error);
            return null;
        }
    }

    /**
     * Update session status and scores
     */
    async updateSession(data) {
        try {
            const response = await fetch(API.GAME_UPDATE, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify(data),
            });
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Error updating session:', error);
            return null;
        }
    }

    /**
     * End game session
     */
    async endGame(sessionId) {
        try {
            const url = API.GAME_END.replace(':id', sessionId);
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error ending game:', error);
            return null;
        }
    }
}

// Export singleton instance
export const api = new GameAPI();
export default api;
