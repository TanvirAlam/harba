import styled from "styled-components";
import Link from "next/link";

export const Container = styled.div`
  min-height: 100vh;
  background: #f9fafb;
`;

export const InnerContainer = styled.div`
  max-width: 80rem;
  margin: 0 auto;
  padding: 3rem 1rem;

  @media (min-width: 640px) {
    padding: 3rem 1.5rem;
  }

  @media (min-width: 1024px) {
    padding: 3rem 2rem;
  }
`;

export const CenterContent = styled.div`
  text-align: center;
`;

export const Title = styled.h1`
  font-size: 2.25rem;
  font-weight: 700;
  color: #111827;

  @media (min-width: 640px) {
    font-size: 3rem;
  }

  @media (min-width: 768px) {
    font-size: 3.75rem;
  }
`;

export const Description = styled.p`
  margin-top: 0.75rem;
  max-width: 28rem;
  margin-left: auto;
  margin-right: auto;
  font-size: 1rem;
  color: #6b7280;

  @media (min-width: 640px) {
    font-size: 1.125rem;
  }

  @media (min-width: 768px) {
    margin-top: 1.25rem;
    font-size: 1.25rem;
    max-width: 48rem;
  }
`;

export const ButtonContainer = styled.div`
  margin-top: 1.25rem;
  max-width: 28rem;
  margin-left: auto;
  margin-right: auto;

  @media (min-width: 640px) {
    display: flex;
    justify-content: center;
  }

  @media (min-width: 768px) {
    margin-top: 2rem;
  }
`;

export const UserSection = styled.div`
  display: flex;
  flex-direction: column;
  gap: 1rem;
`;

export const WelcomeText = styled.p`
  font-size: 1.125rem;
  color: #374151;
`;

export const StyledLink = styled(Link)<{ $primary?: boolean }>`
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.75rem 2rem;
  border: 1px solid ${props => props.$primary ? "transparent" : "#d1d5db"};
  font-size: 1rem;
  font-weight: 500;
  border-radius: 0.375rem;
  color: ${props => props.$primary ? "white" : "#4338ca"};
  background: ${props => props.$primary ? "#4f46e5" : "white"};
  transition: background-color 0.2s;

  &:hover {
    background: ${props => props.$primary ? "#4338ca" : "#f9fafb"};
  }

  @media (min-width: 768px) {
    padding: 1rem 2.5rem;
    font-size: 1.125rem;
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
