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
  CancelButton,
  LoadingContainer,
  LoadingText,
} from "./page.styles";

export default function MyBookingsPage() {
  const [bookings, setBookings] = useState<Booking[]>([]);
  const [loading, setLoading] = useState(true);
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
      setBookings(data);
    } catch (error) {
      console.error("Failed to load bookings", error);
    } finally {
      setLoading(false);
    }
  };

  const cancelBooking = async (bookingId: number) => {
    if (!confirm("Are you sure you want to cancel this booking?")) return;

    try {
      await bookingAPI.cancel(bookingId);
      alert("Booking cancelled successfully!");
      loadBookings(); // Refresh list
    } catch (error) {
      console.error("Failed to cancel booking", error);
      alert("Failed to cancel booking");
    }
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
                    <BookingItem key={booking.id}>
                      <BookingInfo>
                        <BookingTitle>{booking.service}</BookingTitle>
                        <BookingDetail>with {booking.provider}</BookingDetail>
                        <BookingDetail>
                          {new Date(booking.datetime).toLocaleString()}
                        </BookingDetail>
                      </BookingInfo>
                      <CancelButton onClick={() => cancelBooking(booking.id)}>
                        Cancel
                      </CancelButton>
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
