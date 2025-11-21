import styled from "styled-components";

export const Container = styled.div`
  min-height: 100vh;
  background: #f9fafb;
`;

export const Nav = styled.nav`
  background: white;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
`;

export const NavInner = styled.div`
  max-width: 80rem;
  margin: 0 auto;
  padding: 0 1rem;

  @media (min-width: 640px) {
    padding: 0 1.5rem;
  }

  @media (min-width: 1024px) {
    padding: 0 2rem;
  }
`;

export const NavContent = styled.div`
  display: flex;
  justify-content: space-between;
  height: 4rem;
`;

export const NavLeft = styled.div`
  display: flex;
  align-items: center;
`;

export const NavTitle = styled.h1`
  font-size: 1.25rem;
  font-weight: 600;
  color: #111827;
`;

export const NavRight = styled.div`
  display: flex;
  align-items: center;
  gap: 1rem;
`;

export const NavButton = styled.button<{ $primary?: boolean }>`
  color: ${props => props.$primary ? "white" : "#4b5563"};
  background: ${props => props.$primary ? "#2563eb" : "transparent"};
  padding: ${props => props.$primary ? "0.5rem 1rem" : "0"};
  border-radius: ${props => props.$primary ? "0.375rem" : "0"};
  font-size: 0.875rem;
  font-weight: ${props => props.$primary ? "500" : "normal"};
  transition: all 0.2s;

  &:hover {
    color: ${props => props.$primary ? "white" : "#111827"};
    background: ${props => props.$primary ? "#1d4ed8" : "transparent"};
  }
`;

export const Main = styled.main`
  max-width: 80rem;
  margin: 0 auto;
  padding: 1.5rem;

  @media (min-width: 640px) {
    padding: 1.5rem 1.5rem;
  }

  @media (min-width: 1024px) {
    padding: 1.5rem 2rem;
  }
`;

export const Content = styled.div`
  padding: 1.5rem 1rem 0;

  @media (min-width: 640px) {
    padding: 1.5rem 0 0;
  }
`;

export const Card = styled.div`
  background: white;
  overflow: hidden;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
  border-radius: 0.5rem;
`;

export const CardContent = styled.div`
  padding: 1rem 1.5rem;

  @media (min-width: 640px) {
    padding: 1.5rem;
  }
`;

export const CardTitle = styled.h2`
  font-size: 1.125rem;
  font-weight: 500;
  color: #111827;
  margin-bottom: 1rem;
`;

export const EmptyMessage = styled.p`
  color: #6b7280;
`;

export const BookingsList = styled.div`
  display: flex;
  flex-direction: column;
  gap: 1rem;
`;

export const BookingItem = styled.div`
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
`;

export const BookingInfo = styled.div`
  display: flex;
  flex-direction: column;
`;

export const BookingTitle = styled.p`
  font-weight: 500;
  color: #111827;
`;

export const BookingDetail = styled.p`
  font-size: 0.875rem;
  color: #4b5563;
`;

export const CancelButton = styled.button`
  background: #dc2626;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  transition: background-color 0.2s;

  &:hover {
    background: #b91c1c;
  }
`;

export const LoadingContainer = styled.div`
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
`;

export const LoadingText = styled.div`
  font-size: 1.25rem;
`;
