import axios from 'axios';

const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8080'; // Symfony API URL

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Add token to requests if available
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export interface LoginData {
  username: string;
  password: string;
}

export interface RegisterData {
  email: string;
  password: string;
  roles?: string[];
}

export interface User {
  email: string;
  roles: string[];
}

export interface AuthResponse {
  token: string;
}

export interface Service {
  id: number;
  name: string;
  duration: number;
}

export interface Provider {
  id: number;
  name: string;
  workingHours: Record<string, string>;
}

export interface Booking {
  id: number;
  provider: string;
  service: string;
  datetime: string;
  user?: string;
  status: 'confirmed' | 'cancelled';
}

export interface PaginationInfo {
  page: number;
  limit: number;
  total: number;
  pages: number;
}

export interface PaginatedResponse<T> {
  data: T;
  pagination: PaginationInfo;
}

export const authAPI = {
  login: async (data: LoginData): Promise<AuthResponse> => {
    const response = await api.post('/api/login_check', data);
    return response.data;
  },

  register: async (data: RegisterData): Promise<{ message: string }> => {
    const response = await api.post('/api/register', data);
    return response.data;
  },

  getProfile: async (): Promise<User> => {
    const response = await api.get('/api/profile');
    return response.data;
  },
};

export const bookingAPI = {
  getServices: async (): Promise<Service[]> => {
    const response = await api.get('/api/services');
    return response.data;
  },

  getProviders: async (): Promise<Provider[]> => {
    const response = await api.get('/api/providers');
    return response.data;
  },

  getAvailableSlots: async (providerId: number, serviceId: number): Promise<string[]> => {
    const response = await api.get('/api/bookings/available-slots', {
      params: { provider_id: providerId, service_id: serviceId },
    });
    return response.data;
  },

  book: async (data: { provider_id: number; service_id: number; datetime: string }): Promise<{ message: string }> => {
    const response = await api.post('/api/bookings', data);
    return response.data;
  },

  cancel: async (bookingId: number): Promise<{ message: string }> => {
    const response = await api.delete(`/api/bookings/${bookingId}`);
    return response.data;
  },

  hardDelete: async (bookingId: number): Promise<{ message: string }> => {
    const response = await api.delete(`/api/bookings/${bookingId}/hard-delete`);
    return response.data;
  },

  getMyBookings: async (page = 1, limit = 20): Promise<Booking[]> => {
    const response = await api.get(`/api/bookings/my?page=${page}&limit=${limit}`);
    // Handle both old (array) and new (paginated) response formats
    return Array.isArray(response.data) ? response.data : response.data.data;
  },

  getAllBookings: async (page = 1, limit = 50): Promise<Booking[]> => {
    const response = await api.get(`/api/bookings/all?page=${page}&limit=${limit}`);
    // Handle both old (array) and new (paginated) response formats
    return Array.isArray(response.data) ? response.data : response.data.data;
  },
};

export default api;