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
  margin-bottom: 1rem;
  color: #111827;
  display: flex;
  align-items: center;
  gap: 0.5rem;
`;

export const SlotsGrid = styled.div`
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;

  @media (min-width: 640px) {
    grid-template-columns: repeat(2, 1fr);
  }

  @media (min-width: 1024px) {
    grid-template-columns: repeat(3, 1fr);
  }
`;

export const SlotButton = styled.button`
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 1rem 1.5rem;
  font-size: 0.9375rem;
  font-weight: 600;
  color: #374151;
  background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
  border: 2px solid #e5e7eb;
  border-radius: 0.75rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  cursor: pointer;

  &::before {
    content: '🕒';
    font-size: 1.25rem;
  }

  &:hover {
    color: #667eea;
    background: linear-gradient(135deg, #f0f4ff 0%, #e9efff 100%);
    border-color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 8px 12px rgba(102, 126, 234, 0.15);
  }

  &:active {
    transform: translateY(0);
  }
`;
