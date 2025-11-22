"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { bookingAPI, Service, Provider } from "../../lib/api";
import axios from "axios";
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
} from "./page.styles";

interface ToastMessage {
  type: "success" | "error";
  message: string;
}

export default function BookingPage() {
  const [services, setServices] = useState<Service[]>([]);
  const [providers, setProviders] = useState<Provider[]>([]);
  const [selectedProvider, setSelectedProvider] = useState<number | null>(null);
  const [selectedService, setSelectedService] = useState<number | null>(null);
  const [availableSlots, setAvailableSlots] = useState<string[]>([]);
  const [loading, setLoading] = useState(false);
  const [toast, setToast] = useState<ToastMessage | null>(null);
  const [currentPage, setCurrentPage] = useState(1);
  const slotsPerPage = 12;
  const router = useRouter();

  useEffect(() => {
    if (toast) {
      const timer = setTimeout(() => setToast(null), 5000);
      return () => clearTimeout(timer);
    }
  }, [toast]);

  const showToast = (type: "success" | "error", message: string) => {
    setToast({ type, message });
  };

  const getErrorMessage = (error: unknown): string => {
    if (axios.isAxiosError(error)) {
      if (error.response?.data?.message) {
        return error.response.data.message;
      }
      if (error.response?.data?.error) {
        return error.response.data.error;
      }
      if (error.response?.status === 409) {
        return "This time slot is already booked. Please select another slot.";
      }
      if (error.response?.status === 401) {
        return "Your session has expired. Please log in again.";
      }
      if (error.response?.status === 400) {
        return "Invalid booking data. Please check your selection.";
      }
      return `Request failed: ${error.message}`;
    }
    return "An unexpected error occurred. Please try again.";
  };

  useEffect(() => {
    loadData();
  // eslint-disable-next-line react-hooks/exhaustive-deps
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
      showToast("error", getErrorMessage(error));
    }
  };

  const loadSlots = async () => {
    if (!selectedProvider || !selectedService) {
      showToast("error", "Please select both a provider and service");
      return;
    }
    setLoading(true);
    setCurrentPage(1); // Reset to first page when loading new slots
    try {
      const slots = await bookingAPI.getAvailableSlots(
        selectedProvider,
        selectedService
      );
      setAvailableSlots(slots);
      if (slots.length === 0) {
        showToast("error", "No available slots found for this provider and service.");
      }
    } catch (error) {
      console.error("Failed to load slots", error);
      showToast("error", getErrorMessage(error));
    } finally {
      setLoading(false);
    }
  };

  const bookSlot = async (datetime: string) => {
    // Validate selections before booking
    if (!selectedProvider || !selectedService) {
      showToast("error", "Please select both a provider and service");
      return;
    }
    
    try {
      await bookingAPI.book({
        provider_id: selectedProvider!,
        service_id: selectedService!,
        datetime,
      });
      showToast("success", "Booking successful! Redirecting...");
      setTimeout(() => router.push("/my-bookings"), 1500);
    } catch (error) {
      console.error("Failed to book", error);
      showToast("error", getErrorMessage(error));
    }
  };

  return (
    <Container>
      {toast && (
        <div
          style={{
            position: "fixed",
            top: "20px",
            right: "20px",
            padding: "16px 24px",
            borderRadius: "8px",
            backgroundColor: toast.type === "success" ? "#10b981" : "#ef4444",
            color: "white",
            fontWeight: "500",
            boxShadow: "0 4px 6px rgba(0, 0, 0, 0.1)",
            zIndex: 1000,
            maxWidth: "400px",
            animation: "slideIn 0.3s ease-out",
          }}
        >
          {toast.message}
        </div>
      )}
      <Nav>
        <NavInner>
          <NavContent>
            <NavLeft>
              <NavTitle>Book an Appointment</NavTitle>
            </NavLeft>
            <NavRight>
              <NavButton onClick={() => router.push("/dashboard")}>
                <span>← Back to Dashboard</span>
              </NavButton>
            </NavRight>
          </NavContent>
        </NavInner>
      </Nav>

      <Main>
        <Content>
          <FormGroup>
            <Label htmlFor="provider-select">Select Provider:</Label>
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
            <Label htmlFor="service-select">Select Service:</Label>
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
                    onClick={() => bookSlot(slot)}
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
