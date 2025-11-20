import { render, screen, fireEvent, waitFor, act } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import BookingPage from '../../../app/booking/page';
import { bookingAPI } from '../../../lib/api';

// Mock the API
jest.mock('../../../lib/api', () => ({
  bookingAPI: {
    getServices: jest.fn(),
    getProviders: jest.fn(),
    getAvailableSlots: jest.fn(),
    book: jest.fn(),
  },
}));

// Mock Next.js router
const mockPush = jest.fn();
jest.mock('next/navigation', () => ({
  useRouter: () => ({
    push: mockPush,
  }),
}));

describe('BookingPage', () => {
  beforeEach(() => {
    jest.clearAllMocks();

    // Mock API responses
    bookingAPI.getServices.mockResolvedValue([
      { id: 1, name: 'Haircut', duration: 30 },
      { id: 2, name: 'Massage', duration: 60 },
    ]);

    bookingAPI.getProviders.mockResolvedValue([
      { id: 1, name: 'John Doe', workingHours: { monday: '09:00-17:00' } },
      { id: 2, name: 'Jane Smith', workingHours: { monday: '10:00-18:00' } },
    ]);
  });

  it('renders the booking page with title', () => {
    render(<BookingPage />);

    expect(screen.getByText('Book an Appointment')).toBeInTheDocument();
  });

  it('loads services and providers on mount', async () => {
    await act(async () => {
      render(<BookingPage />);
    });

    await waitFor(() => {
      expect(bookingAPI.getServices).toHaveBeenCalledTimes(1);
      expect(bookingAPI.getProviders).toHaveBeenCalledTimes(1);
    });

    expect(screen.getByText('Haircut (30 min)')).toBeInTheDocument();
    expect(screen.getByText('Massage (60 min)')).toBeInTheDocument();
    expect(screen.getByText('John Doe')).toBeInTheDocument();
    expect(screen.getByText('Jane Smith')).toBeInTheDocument();
  });

  it('shows available slots when provider and service are selected', async () => {
    const user = userEvent.setup();

    bookingAPI.getAvailableSlots.mockResolvedValue([
      '2023-12-01 10:00:00',
      '2023-12-01 10:30:00',
    ]);

    render(<BookingPage />);

    await waitFor(() => {
      expect(bookingAPI.getServices).toHaveBeenCalled();
    });

    // Select provider
    const providerSelect = screen.getByLabelText('Select Provider:');
    fireEvent.change(providerSelect, { target: { value: '1' } });

    // Select service
    const serviceSelect = screen.getByLabelText('Select Service:');
    fireEvent.change(serviceSelect, { target: { value: '1' } });

    // Click show slots button
    const showSlotsButton = screen.getByText('Show Available Slots');
    await user.click(showSlotsButton);

    await waitFor(() => {
      expect(bookingAPI.getAvailableSlots).toHaveBeenCalledWith(1, 1);
    });

    expect(screen.getByText('Available Slots')).toBeInTheDocument();
    expect(screen.getAllByRole('button', { name: /Dec 1, 2023/ })).toHaveLength(2);
  });

  it('books a slot when clicked', async () => {
    const user = userEvent.setup();

    bookingAPI.getAvailableSlots.mockResolvedValue(['2023-12-01 10:00:00']);
    bookingAPI.book.mockResolvedValue({ message: 'Booking created' });

    render(<BookingPage />);

    await waitFor(() => {
      expect(bookingAPI.getServices).toHaveBeenCalled();
    });

    // Select provider and service
    const providerSelect = screen.getByLabelText('Select Provider:');
    await user.selectOptions(providerSelect, '1');

    const serviceSelect = screen.getByLabelText('Select Service:');
    await user.selectOptions(serviceSelect, '1');

    // Show slots
    const showSlotsButton = screen.getByText('Show Available Slots');
    await user.click(showSlotsButton);

    await waitFor(() => {
      expect(bookingAPI.getAvailableSlots).toHaveBeenCalled();
    });

    // Click on slot to book
    const slotButton = screen.getByRole('button', { name: /Dec 1, 2023/ });
    await user.click(slotButton);

    await waitFor(() => {
      expect(bookingAPI.book).toHaveBeenCalledWith({
        provider_id: 1,
        service_id: 1,
        datetime: '2023-12-01 10:00:00',
      });
    });

    expect(screen.getByText('Booking successful!')).toBeInTheDocument();
  });

  it('handles booking errors', async () => {
    const user = userEvent.setup();

    bookingAPI.getAvailableSlots.mockResolvedValue(['2023-12-01 10:00:00']);
    bookingAPI.book.mockRejectedValue(new Error('Booking failed'));

    render(<BookingPage />);

    await waitFor(() => {
      expect(bookingAPI.getServices).toHaveBeenCalled();
    });

    // Select provider and service
    const providerSelect = screen.getByLabelText('Select Provider:');
    await user.selectOptions(providerSelect, '1');

    const serviceSelect = screen.getByLabelText('Select Service:');
    await user.selectOptions(serviceSelect, '1');

    // Show slots
    const showSlotsButton = screen.getByText('Show Available Slots');
    await user.click(showSlotsButton);

    await waitFor(() => {
      expect(bookingAPI.getAvailableSlots).toHaveBeenCalled();
    });

    // Click on slot to book
    const slotButton = screen.getByRole('button', { name: /Dec 1, 2023/ });
    await user.click(slotButton);

    await waitFor(() => {
      expect(bookingAPI.book).toHaveBeenCalled();
    });

    expect(screen.getByText('Booking failed')).toBeInTheDocument();
  });

  it('disables show slots button when provider or service not selected', async () => {
    render(<BookingPage />);

    await waitFor(() => {
      expect(bookingAPI.getServices).toHaveBeenCalled();
    });

    const showSlotsButton = screen.getByText('Show Available Slots');
    expect(showSlotsButton).toBeDisabled();

    // Select only provider
    const providerSelect = screen.getByLabelText('Select Provider:');
    fireEvent.change(providerSelect, { target: { value: '1' } });

    expect(showSlotsButton).toBeDisabled();

    // Select service
    const serviceSelect = screen.getByLabelText('Select Service:');
    fireEvent.change(serviceSelect, { target: { value: '1' } });

    expect(showSlotsButton).not.toBeDisabled();
  });
});