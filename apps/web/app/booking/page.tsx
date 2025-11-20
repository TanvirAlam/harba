"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { bookingAPI, Service, Provider } from "../../lib/api";

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
      loadSlots(); // Refresh slots
    } catch (error) {
      console.error("Failed to book", error);
      alert("Booking failed");
    }
  };

  return (
    <div className="container mx-auto p-4">
      <h1 className="text-2xl font-bold mb-4">Book an Appointment</h1>

      <div className="mb-4">
        <label htmlFor="provider-select" className="block mb-2">
          Select Provider:
        </label>
        <select
          id="provider-select"
          value={selectedProvider || ""}
          onChange={(e) => setSelectedProvider(Number(e.target.value))}
          className="border p-2 w-full"
        >
          <option value="">Choose a provider</option>
          {providers.map((provider) => (
            <option key={provider.id} value={provider.id}>
              {provider.name}
            </option>
          ))}
        </select>
      </div>

      <div className="mb-4">
        <label htmlFor="service-select" className="block mb-2">
          Select Service:
        </label>
        <select
          id="service-select"
          value={selectedService || ""}
          onChange={(e) => setSelectedService(Number(e.target.value))}
          className="border p-2 w-full"
        >
          <option value="">Choose a service</option>
          {services.map((service) => (
            <option key={service.id} value={service.id}>
              {service.name} ({service.duration} min)
            </option>
          ))}
        </select>
      </div>

      <button
        onClick={loadSlots}
        disabled={!selectedProvider || !selectedService || loading}
        className="bg-blue-500 text-white px-4 py-2 rounded disabled:opacity-50"
      >
        {loading ? "Loading..." : "Show Available Slots"}
      </button>

      <div className="mt-4">
        <h2 className="text-xl font-semibold mb-2">Available Slots</h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-2">
          {availableSlots.map((slot) => (
            <button
              key={slot}
              onClick={() => bookSlot(slot)}
              className="border p-2 rounded hover:bg-gray-100"
            >
              {new Date(slot).toLocaleString()}
            </button>
          ))}
        </div>
      </div>
    </div>
  );
}
