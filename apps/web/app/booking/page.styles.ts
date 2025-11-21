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
`;

export const NavButton = styled.button`
  color: #4b5563;
  transition: color 0.2s;

  &:hover {
    color: #111827;
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

export const FormGroup = styled.div`
  margin-bottom: 1rem;
`;

export const Label = styled.label`
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #374151;
`;

export const Select = styled.select`
  border: 1px solid #d1d5db;
  padding: 0.5rem;
  width: 100%;
  border-radius: 0.375rem;
  background: white;
  color: #111827;
  transition: border-color 0.2s;

  &:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }
`;

export const Button = styled.button<{ $disabled?: boolean }>`
  background: ${props => props.$disabled ? "#93c5fd" : "#3b82f6"};
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-weight: 500;
  transition: background-color 0.2s;
  cursor: ${props => props.$disabled ? "not-allowed" : "pointer"};

  &:hover {
    background: ${props => props.$disabled ? "#93c5fd" : "#2563eb"};
  }
`;

export const SlotsContainer = styled.div`
  margin-top: 1rem;
`;

export const SlotsTitle = styled.h2`
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #111827;
`;

export const SlotsGrid = styled.div`
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.5rem;

  @media (min-width: 768px) {
    grid-template-columns: repeat(3, 1fr);
  }
`;

export const SlotButton = styled.button`
  border: 1px solid #d1d5db;
  padding: 0.5rem;
  border-radius: 0.375rem;
  background: white;
  transition: background-color 0.2s;

  &:hover {
    background: #f3f4f6;
  }
`;
