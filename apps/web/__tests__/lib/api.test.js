import axios from 'axios';
import { bookingAPI } from '../../lib/api';

// Mock axios
jest.mock('axios');
const mockedAxios = axios;

// Mock the api instance
const mockApiInstance = {
  get: jest.fn(),
  post: jest.fn(),
  delete: jest.fn(),
};
mockedAxios.create.mockReturnValue(mockApiInstance);

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
      mockedAxios.create.mockReturnValue({
        get: jest.fn().mockRejectedValue(new Error(errorMessage)),
      });

      await expect(bookingAPI.getServices()).rejects.toThrow(errorMessage);
    });
  });

  describe('getProviders', () => {
    it('should fetch providers successfully', async () => {
      const mockProviders = [
        { id: 1, name: 'John Doe', workingHours: { monday: '09:00-17:00' } },
      ];

      mockedAxios.create.mockReturnValue({
        get: jest.fn().mockResolvedValue({ data: mockProviders }),
      });

      const result = await bookingAPI.getProviders();

      expect(result).toEqual(mockProviders);
    });
  });

  describe('getAvailableSlots', () => {
    it('should fetch available slots with correct params', async () => {
      const mockSlots = ['2023-12-01 10:00:00', '2023-12-01 10:30:00'];
      const providerId = 1;
      const serviceId = 2;

      mockedAxios.create.mockReturnValue({
        get: jest.fn().mockResolvedValue({ data: mockSlots }),
      });

      const result = await bookingAPI.getAvailableSlots(providerId, serviceId);

      expect(result).toEqual(mockSlots);
      expect(mockedAxios.create().get).toHaveBeenCalledWith('/api/bookings/available-slots', {
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

      mockedAxios.create.mockReturnValue({
        post: jest.fn().mockResolvedValue({ data: mockResponse }),
      });

      const result = await bookingAPI.book(bookingData);

      expect(result).toEqual(mockResponse);
      expect(mockedAxios.create().post).toHaveBeenCalledWith('/api/bookings', bookingData);
    });
  });

  describe('cancel', () => {
    it('should cancel booking successfully', async () => {
      const bookingId = 123;
      const mockResponse = { message: 'Booking cancelled' };

      mockedAxios.create.mockReturnValue({
        delete: jest.fn().mockResolvedValue({ data: mockResponse }),
      });

      const result = await bookingAPI.cancel(bookingId);

      expect(result).toEqual(mockResponse);
      expect(mockedAxios.create().delete).toHaveBeenCalledWith(`/api/bookings/${bookingId}`);
    });
  });

  describe('getMyBookings', () => {
    it('should fetch user bookings', async () => {
      const mockBookings = [
        { id: 1, provider: 'John Doe', service: 'Haircut', datetime: '2023-12-01 10:00:00' },
      ];

      mockedAxios.create.mockReturnValue({
        get: jest.fn().mockResolvedValue({ data: mockBookings }),
      });

      const result = await bookingAPI.getMyBookings();

      expect(result).toEqual(mockBookings);
      expect(mockedAxios.create().get).toHaveBeenCalledWith('/api/bookings/my');
    });
  });

  describe('getAllBookings', () => {
    it('should fetch all bookings for admin', async () => {
      const mockBookings = [
        { id: 1, user: 'user@example.com', provider: 'John Doe', service: 'Haircut', datetime: '2023-12-01 10:00:00' },
      ];

      mockedAxios.create.mockReturnValue({
        get: jest.fn().mockResolvedValue({ data: mockBookings }),
      });

      const result = await bookingAPI.getAllBookings();

      expect(result).toEqual(mockBookings);
      expect(mockedAxios.create().get).toHaveBeenCalledWith('/api/bookings/all');
    });
  });
});