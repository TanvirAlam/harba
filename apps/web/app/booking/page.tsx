"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { bookingAPI, Service, Provider } from "../../lib/api";
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

export default function BookingPage() {
  const [services, setServices] = useState<Service[]>([]);
  const [providers, setProviders] = useState<Provider[]>([]);
  const [selectedProvider, setSelectedProvider] = useState<number | null>(null);
  const [selectedService, setSelectedService] = useState<number | null>(null);
  const [availableSlots, setAvailableSlots] = useState<string[]>([]);
  const [loading, setLoading] = useState(false);
  const router = useRouter();

  useEffect(() => {
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

  const bookSlot = async (datetime: string) => {
    try {
      await bookingAPI.book({
        provider_id: selectedProvider!,
        service_id: selectedService!,
        datetime,
      });
      alert("Booking successful!");
      router.push("/my-bookings"); // Redirect to my bookings
    } catch (error) {
      console.error("Failed to book", error);
      alert("Booking failed");
    }
  };

  return (
    <Container>
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
            <SlotsTitle>Available Slots</SlotsTitle>
            <SlotsGrid>
              {availableSlots.map((slot) => (
                <SlotButton
                  key={slot}
                  onClick={() => bookSlot(slot)}
                >
                  {new Date(slot).toLocaleString()}
                </SlotButton>
              ))}
            </SlotsGrid>
          </SlotsContainer>
        </Content>
      </Main>
    </Container>
  );
}
