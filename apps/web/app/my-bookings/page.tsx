"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { useAuth } from "../../contexts/AuthContext";
import { bookingAPI, Booking } from "../../lib/api";
import {
  Container,
  Nav,
  NavInner,
  NavContent,
  NavLeft,
  NavTitle,
  NavRight,
  NavButton,
  Main,
  Content,
  Card,
  CardContent,
  CardTitle,
  EmptyMessage,
  BookingsList,
  BookingItem,
  BookingInfo,
  BookingTitle,
  BookingDetail,
  ButtonGroup,
  EditButton,
  CancelButton,
  DeleteButton,
  CancelledBadge,
  LoadingContainer,
  LoadingText,
} from "./page.styles";

export default function MyBookingsPage() {
  const [bookings, setBookings] = useState<Booking[]>([]);
  const [loading, setLoading] = useState(true);
  const [cancelingId, setCancelingId] = useState<number | null>(null);
  const { user } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (!user) {
      router.push("/login");
      return;
    }
    loadBookings();
  }, [user, router]);

  const loadBookings = async () => {
    try {
      const data = await bookingAPI.getMyBookings();
      console.log('Loaded bookings:', data);
      setBookings(data);
    } catch (error) {
      console.error("Failed to load bookings", error);
    } finally {
      setLoading(false);
    }
  };

  const cancelBooking = async (bookingId: number) => {
    console.log('cancelBooking called for ID:', bookingId);
    const confirmed = confirm("Are you sure you want to cancel this booking?");
    console.log('User confirmed:', confirmed);
    
    if (!confirmed) {
      console.log('User cancelled the dialog');
      return;
    }

    console.log('Setting cancelingId to:', bookingId);
    setCancelingId(bookingId);
    try {
      console.log('Calling API to cancel booking...');
      const result = await bookingAPI.cancel(bookingId);
      console.log('Cancel result:', result);
      // Show success message
      alert("✓ Booking cancelled successfully!");
      // Refresh the list
      console.log('Reloading bookings...');
      await loadBookings();
    } catch (error: any) {
      console.error("Failed to cancel booking", error);
      const errorMsg = error?.response?.data?.error || "Failed to cancel booking";
      alert("✗ " + errorMsg);
    } finally {
      console.log('Resetting cancelingId');
      setCancelingId(null);
    }
  };

  const deleteBooking = async (bookingId: number) => {
    console.log('deleteBooking called for ID:', bookingId);
    const confirmed = confirm("Are you sure you want to permanently delete this booking? This cannot be undone.");
    console.log('Delete confirmed:', confirmed);
    
    if (!confirmed) {
      console.log('User cancelled delete');
      return;
    }

    console.log('Setting cancelingId for delete');
    setCancelingId(bookingId);
    try {
      console.log('Calling hardDelete API...');
      const result = await bookingAPI.hardDelete(bookingId);
      console.log('Hard delete result:', result);
      alert("✓ Booking permanently deleted!");
      console.log('Reloading bookings after delete...');
      await loadBookings();
      console.log('Bookings reloaded');
    } catch (error: any) {
      console.error("Failed to delete booking", error);
      console.error('Error details:', error?.response?.data);
      const errorMsg = error?.response?.data?.error || "Failed to delete booking";
      alert("✗ " + errorMsg);
    } finally {
      console.log('Resetting cancelingId after delete');
      setCancelingId(null);
    }
  };

  const editBooking = (booking: Booking) => {
    // Store booking data in sessionStorage for the edit page
    sessionStorage.setItem('editBooking', JSON.stringify(booking));
    router.push('/booking/edit');
  };

  if (loading) {
    return (
      <LoadingContainer>
        <LoadingText>Loading...</LoadingText>
      </LoadingContainer>
    );
  }

  return (
    <Container>
      <Nav>
        <NavInner>
          <NavContent>
            <NavLeft>
              <NavTitle>My Bookings</NavTitle>
            </NavLeft>
            <NavRight>
              <NavButton onClick={() => router.push("/dashboard")}>
                <span>← Dashboard</span>
              </NavButton>
              <NavButton onClick={() => router.push("/booking")}>
                <span>➕ Book New</span>
              </NavButton>
            </NavRight>
          </NavContent>
        </NavInner>
      </Nav>

      <Main>
        <Content>
          <Card>
            <CardContent>
              <CardTitle>Your Bookings</CardTitle>
              {bookings.length === 0 ? (
                <EmptyMessage>No bookings found.</EmptyMessage>
              ) : (
                <BookingsList>
                  {bookings.map((booking) => (
                    <BookingItem key={booking.id} $cancelled={booking.status === 'cancelled'}>
                      <BookingInfo>
                        <BookingTitle>
                          {booking.service}
                          {booking.status === 'cancelled' && <CancelledBadge>CANCELLED</CancelledBadge>}
                        </BookingTitle>
                        <BookingDetail>with {booking.provider}</BookingDetail>
                        <BookingDetail>
                          {new Date(booking.datetime).toLocaleString()}
                        </BookingDetail>
                      </BookingInfo>
                      <ButtonGroup>
                        {booking.status === 'cancelled' ? (
                          <DeleteButton 
                            onClick={() => deleteBooking(booking.id)}
                            disabled={cancelingId === booking.id}
                          >
                            {cancelingId === booking.id ? "Deleting..." : "Delete"}
                          </DeleteButton>
                        ) : (
                          <>
                            <EditButton 
                              onClick={() => editBooking(booking)}
                              disabled={cancelingId === booking.id}
                            >
                              Edit
                            </EditButton>
                            <CancelButton 
                              onClick={() => cancelBooking(booking.id)}
                              disabled={cancelingId === booking.id}
                            >
                              {cancelingId === booking.id ? "Canceling..." : "Cancel"}
                            </CancelButton>
                          </>
                        )}
                      </ButtonGroup>
                    </BookingItem>
                  ))}
                </BookingsList>
              )}
            </CardContent>
          </Card>
        </Content>
      </Main>
    </Container>
  );
}
