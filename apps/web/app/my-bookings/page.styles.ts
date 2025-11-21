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
  position: relative;
  padding: 0.625rem 1.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: white;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 0.75rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 6px -1px rgba(102, 126, 234, 0.3), 0 2px 4px -1px rgba(102, 126, 234, 0.2);
  overflow: hidden;

  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(102, 126, 234, 0.4), 0 4px 6px -2px rgba(102, 126, 234, 0.3);

    &::before {
      opacity: 1;
    }
  }

  &:active {
    transform: translateY(0);
  }

  span {
    position: relative;
    z-index: 1;
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
