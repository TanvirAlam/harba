"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { bookingAPI, Service, Provider, Booking } from "../../../lib/api";
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
  FormGroup,
  Label,
  Select,
  Button,
  SlotsContainer,
  SlotsTitle,
  SlotsGrid,
  SlotButton,
  InfoBox,
} from "../page.styles";

export default function EditBookingPage() {
  const [services, setServices] = useState<Service[]>([]);
  const [providers, setProviders] = useState<Provider[]>([]);
  const [selectedProvider, setSelectedProvider] = useState<number | null>(null);
  const [selectedService, setSelectedService] = useState<number | null>(null);
  const [availableSlots, setAvailableSlots] = useState<string[]>([]);
  const [loading, setLoading] = useState(false);
  const [originalBooking, setOriginalBooking] = useState<Booking | null>(null);
  const [currentPage, setCurrentPage] = useState(1);
  const slotsPerPage = 12;
  const router = useRouter();

  useEffect(() => {
    // Get booking from sessionStorage
    const bookingData = sessionStorage.getItem('editBooking');
    if (!bookingData) {
      router.push('/my-bookings');
      return;
    }
    
    const booking: Booking = JSON.parse(bookingData);
    setOriginalBooking(booking);
    
    loadData();
  }, []);

  const loadData = async () => {
    try {
      const [servicesData, providersData] = await Promise.all([
        bookingAPI.getServices(),
        bookingAPI.getProviders(),
      ]);
      setServices(servicesData);
      setProviders(providersData);
    } catch (error) {
      console.error("Failed to load data", error);
    }
  };

  const loadSlots = async () => {
    if (!selectedProvider || !selectedService) return;
    setLoading(true);
    setCurrentPage(1); // Reset to first page when loading new slots
    try {
      const slots = await bookingAPI.getAvailableSlots(
        selectedProvider,
        selectedService
      );
      setAvailableSlots(slots);
    } catch (error) {
      console.error("Failed to load slots", error);
    } finally {
      setLoading(false);
    }
  };

  const updateBooking = async (datetime: string) => {
    if (!originalBooking) return;
    
    try {
      // Cancel old booking and create new one
      await bookingAPI.cancel(originalBooking.id);
      await bookingAPI.book({
        provider_id: selectedProvider!,
        service_id: selectedService!,
        datetime,
      });
      
      sessionStorage.removeItem('editBooking');
      alert("Booking updated successfully!");
      router.push("/my-bookings");
    } catch (error: any) {
      console.error("Failed to update booking", error);
      const errorMsg = error?.response?.data?.error || "Failed to update booking";
      alert(errorMsg);
    }
  };

  if (!originalBooking) {
    return null;
  }

  return (
    <Container>
      <Nav>
        <NavInner>
          <NavContent>
            <NavLeft>
              <NavTitle>Edit Booking</NavTitle>
            </NavLeft>
            <NavRight>
              <NavButton onClick={() => {
                sessionStorage.removeItem('editBooking');
                router.push("/my-bookings");
              }}>
                <span>← Back to Bookings</span>
              </NavButton>
            </NavRight>
          </NavContent>
        </NavInner>
      </Nav>

      <Main>
        <Content>
          <InfoBox>
            <strong>Current Booking:</strong> {originalBooking.service} with {originalBooking.provider} on{" "}
            {new Date(originalBooking.datetime).toLocaleString()}
          </InfoBox>

          <FormGroup>
            <Label htmlFor="provider-select">Select New Provider:</Label>
            <Select
              id="provider-select"
              value={selectedProvider || ""}
              onChange={(e) => setSelectedProvider(Number(e.target.value))}
            >
              <option value="">Choose a provider</option>
              {providers.map((provider) => (
                <option key={provider.id} value={provider.id}>
                  {provider.name}
                </option>
              ))}
            </Select>
          </FormGroup>

          <FormGroup>
            <Label htmlFor="service-select">Select New Service:</Label>
            <Select
              id="service-select"
              value={selectedService || ""}
              onChange={(e) => setSelectedService(Number(e.target.value))}
            >
              <option value="">Choose a service</option>
              {services.map((service) => (
                <option key={service.id} value={service.id}>
                  {service.name} ({service.duration} min)
                </option>
              ))}
            </Select>
          </FormGroup>

          <Button
            onClick={loadSlots}
            disabled={!selectedProvider || !selectedService || loading}
            $disabled={!selectedProvider || !selectedService || loading}
          >
            {loading ? "Loading..." : "Show Available Slots"}
          </Button>

          <SlotsContainer>
            <SlotsTitle>
              📅 Available Slots {availableSlots.length > 0 && `(${availableSlots.length} total)`}
            </SlotsTitle>
            <SlotsGrid>
              {availableSlots
                .slice((currentPage - 1) * slotsPerPage, currentPage * slotsPerPage)
                .map((slot) => (
                  <SlotButton
                    key={slot}
                    onClick={() => updateBooking(slot)}
                  >
                    {new Date(slot).toLocaleString('en-US', {
                      weekday: 'short',
                      month: 'short',
                      day: 'numeric',
                      hour: 'numeric',
                      minute: '2-digit',
                      hour12: true
                    })}
                  </SlotButton>
                ))}
            </SlotsGrid>
            {availableSlots.length > slotsPerPage && (
              <div style={{
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'center',
                gap: '12px',
                marginTop: '24px',
                flexWrap: 'wrap'
              }}>
                <Button
                  onClick={() => setCurrentPage(prev => Math.max(1, prev - 1))}
                  disabled={currentPage === 1}
                  $disabled={currentPage === 1}
                  style={{ minWidth: '100px' }}
                >
                  Previous
                </Button>
                <span style={{ color: '#6b7280', fontSize: '14px', fontWeight: '500' }}>
                  Page {currentPage} of {Math.ceil(availableSlots.length / slotsPerPage)}
                </span>
                <Button
                  onClick={() => setCurrentPage(prev => Math.min(Math.ceil(availableSlots.length / slotsPerPage), prev + 1))}
                  disabled={currentPage === Math.ceil(availableSlots.length / slotsPerPage)}
                  $disabled={currentPage === Math.ceil(availableSlots.length / slotsPerPage)}
                  style={{ minWidth: '100px' }}
                >
                  Next
                </Button>
              </div>
            )}
          </SlotsContainer>
        </Content>
      </Main>
    </Container>
  );
}
