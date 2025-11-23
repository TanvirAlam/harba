// Mock axios - create a shared mock instance
jest.mock('axios', () => {
  const mockInstance = {
    get: jest.fn(),
    post: jest.fn(),
    delete: jest.fn(),
    interceptors: {
      request: {
        use: jest.fn(),
      },
      response: {
        use: jest.fn(),
      },
    },
  };
  
  return {
    __esModule: true,
    default: {
      create: jest.fn(() => mockInstance),
      _mockInstance: mockInstance, // Expose for tests
    },
  };
});

import axios from 'axios';
import { bookingAPI } from '../../lib/api';

// Get reference to the mock instance
const mockApiInstance = axios._mockInstance;
const mockedAxios = axios;

describe('bookingAPI', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    // Mock localStorage
    Object.defineProperty(window, 'localStorage', {
      value: {
        getItem: jest.fn(() => 'mock-token'),
        setItem: jest.fn(),
        removeItem: jest.fn(),
      },
      writable: true,
    });
  });

  describe('getServices', () => {
    it('should fetch services successfully', async () => {
      const mockServices = [
        { id: 1, name: 'Haircut', duration: 30 },
        { id: 2, name: 'Massage', duration: 60 },
      ];

      mockApiInstance.get.mockResolvedValue({ data: mockServices });

      const result = await bookingAPI.getServices();

      expect(result).toEqual(mockServices);
      expect(mockApiInstance.get).toHaveBeenCalledWith('/api/services');
    });

  it('should handle API errors', async () => {
      const errorMessage = 'Network Error';
      mockApiInstance.get.mockRejectedValue(new Error(errorMessage));

      await expect(bookingAPI.getServices()).rejects.toThrow(errorMessage);
    });
  });

  describe('getProviders', () => {
    it('should fetch providers successfully', async () => {
      const mockProviders = [
        { id: 1, name: 'John Doe', workingHours: { monday: '09:00-17:00' } },
      ];

      mockApiInstance.get.mockResolvedValue({ data: mockProviders });

      const result = await bookingAPI.getProviders();

      expect(result).toEqual(mockProviders);
      expect(mockApiInstance.get).toHaveBeenCalledWith('/api/providers');
    });
  });

  describe('getAvailableSlots', () => {
    it('should fetch available slots with correct params', async () => {
      const mockSlots = ['2023-12-01 10:00:00', '2023-12-01 10:30:00'];
      const providerId = 1;
      const serviceId = 2;

      mockApiInstance.get.mockResolvedValue({ data: mockSlots });

      const result = await bookingAPI.getAvailableSlots(providerId, serviceId);

      expect(result).toEqual(mockSlots);
      expect(mockApiInstance.get).toHaveBeenCalledWith('/api/bookings/available-slots', {
        params: { provider_id: providerId, service_id: serviceId },
      });
    });
  });

  describe('book', () => {
    it('should book appointment successfully', async () => {
      const bookingData = {
        provider_id: 1,
        service_id: 2,
        datetime: '2023-12-01 10:00:00',
      };
      const mockResponse = { message: 'Booking created' };

      mockApiInstance.post.mockResolvedValue({ data: mockResponse });

      const result = await bookingAPI.book(bookingData);

      expect(result).toEqual(mockResponse);
      expect(mockApiInstance.post).toHaveBeenCalledWith('/api/bookings', bookingData);
    });
  });

  describe('cancel', () => {
    it('should cancel booking successfully', async () => {
      const bookingId = 123;
      const mockResponse = { message: 'Booking cancelled' };

      mockApiInstance.delete.mockResolvedValue({ data: mockResponse });

      const result = await bookingAPI.cancel(bookingId);

      expect(result).toEqual(mockResponse);
      expect(mockApiInstance.delete).toHaveBeenCalledWith(`/api/bookings/${bookingId}`);
    });
  });

  describe('getMyBookings', () => {
    it('should fetch user bookings', async () => {
      const mockBookings = [
        { id: 1, provider: 'John Doe', service: 'Haircut', datetime: '2023-12-01 10:00:00' },
      ];

      mockApiInstance.get.mockResolvedValue({ data: mockBookings });

      const result = await bookingAPI.getMyBookings();

      expect(result).toEqual(mockBookings);
      expect(mockApiInstance.get).toHaveBeenCalledWith('/api/bookings/my?page=1&limit=20');
    });
  });

  describe('getAllBookings', () => {
    it('should fetch all bookings for admin', async () => {
      const mockBookings = [
        { id: 1, user: 'user@example.com', provider: 'John Doe', service: 'Haircut', datetime: '2023-12-01 10:00:00' },
      ];

      mockApiInstance.get.mockResolvedValue({ data: mockBookings });

      const result = await bookingAPI.getAllBookings();

      expect(result).toEqual(mockBookings);
      expect(mockApiInstance.get).toHaveBeenCalledWith('/api/bookings/all?page=1&limit=50');
    });
  });
});