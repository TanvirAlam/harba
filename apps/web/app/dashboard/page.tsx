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
  LogoutButton,
  Main,
  Content,
  Card,
  CardContent,
  CardTitle,
  ProfileSection,
  ProfileField,
  FieldLabel,
  FieldValue,
  RolesList,
  RoleBadge,
  LoadingMessage,
  EmptyMessage,
  BookingsList,
  BookingItem,
  BookingHeader,
  BookingInfo,
  BookingTitle,
  BookingDetail,
  BookingUser,
  StatusBadge,
  LoadingContainer,
  LoadingText,
} from "./page.styles";

export default function DashboardPage() {
  const { user, logout, loading } = useAuth();
  const router = useRouter();
  const [allBookings, setAllBookings] = useState<Booking[]>([]);
  const [bookingsLoading, setBookingsLoading] = useState(false);

  useEffect(() => {
    if (!loading && !user) {
      router.push("/login");
      return;
    }
    if (user) {
      loadAllBookings();
    }
  }, [user, loading, router]);

  const loadAllBookings = async () => {
    setBookingsLoading(true);
    try {
      const data = await bookingAPI.getAllBookings();
      setAllBookings(data);
    } catch (error) {
      console.error("Failed to load all bookings", error);
    } finally {
      setBookingsLoading(false);
    }
  };

  if (loading) {
    return (
      <LoadingContainer>
        <LoadingText>Loading...</LoadingText>
      </LoadingContainer>
    );
  }

  if (!user) {
    return null;
  }

  return (
    <Container>
      <Nav>
        <NavInner>
          <NavContent>
            <NavLeft>
              <NavTitle>Dashboard</NavTitle>
            </NavLeft>
            <NavRight>
              <NavButton onClick={() => router.push("/booking")}>
                <span>📅 Book Appointment</span>
              </NavButton>
              <NavButton onClick={() => router.push("/my-bookings")}>
                <span>📋 My Bookings</span>
              </NavButton>
              <LogoutButton onClick={logout}>Logout</LogoutButton>
            </NavRight>
          </NavContent>
        </NavInner>
      </Nav>

      <Main>
        <Content>
          <Card>
            <CardContent>
              <CardTitle>Profile Information</CardTitle>
              <ProfileSection>
                <ProfileField>
                  <FieldLabel>Email</FieldLabel>
                  <FieldValue>{user.email}</FieldValue>
                </ProfileField>
                <ProfileField>
                  <FieldLabel>Roles</FieldLabel>
                  <RolesList>
                    {user.roles.map((role) => (
                      <RoleBadge key={role}>{role}</RoleBadge>
                    ))}
                  </RolesList>
                </ProfileField>
              </ProfileSection>
            </CardContent>
          </Card>

          {user && (
            <Card>
              <CardContent>
                <CardTitle>All Bookings (Admin)</CardTitle>
                {bookingsLoading ? (
                  <LoadingMessage>Loading bookings...</LoadingMessage>
                ) : allBookings.length === 0 ? (
                  <EmptyMessage>No bookings found.</EmptyMessage>
                ) : (
                  <BookingsList>
                    {allBookings.map((booking) => (
                      <BookingItem key={booking.id} $status={booking.status}>
                        <BookingHeader>
                          <BookingInfo>
                            <BookingTitle>{booking.service}</BookingTitle>
                            <BookingDetail>
                              with {booking.provider}
                            </BookingDetail>
                            <BookingDetail>
                              {new Date(booking.datetime).toLocaleString()}
                            </BookingDetail>
                            <BookingUser>User: {booking.user}</BookingUser>
                            <StatusBadge $status={booking.status}>
                              {booking.status}
                            </StatusBadge>
                          </BookingInfo>
                        </BookingHeader>
                      </BookingItem>
                    ))}
                  </BookingsList>
                )}
              </CardContent>
            </Card>
          )}
        </Content>
      </Main>
    </Container>
  );
}
