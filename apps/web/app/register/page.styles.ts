import styled from "styled-components";
import Link from "next/link";

export const Container = styled.div`
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(to bottom right, #f0fdf4, #d1fae5);
  padding: 1rem 1rem;

  @media (min-width: 640px) {
    padding: 1rem 1.5rem;
  }

  @media (min-width: 1024px) {
    padding: 1rem 2rem;
  }
`;

export const Content = styled.div`
  max-width: 28rem;
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 2rem;
`;

export const Card = styled.div`
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  padding: 2rem;
  border: 1px solid #e5e7eb;
`;

export const Header = styled.div`
  text-align: center;
`;

export const IconContainer = styled.div`
  margin: 0 auto;
  height: 3rem;
  width: 3rem;
  background: #059669;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1rem;
`;

export const Icon = styled.svg`
  height: 1.5rem;
  width: 1.5rem;
  color: white;
`;

export const Title = styled.h2`
  font-size: 1.875rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 0.5rem;
`;

export const Subtitle = styled.p`
  color: #4b5563;
`;

export const Form = styled.form`
  margin-top: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
`;

export const ErrorAlert = styled.div`
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
  padding: 1rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
`;

export const SuccessAlert = styled.div`
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  color: #16a34a;
  padding: 1rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
`;

export const FormFields = styled.div`
  display: flex;
  flex-direction: column;
  gap: 1rem;
`;

export const FormGroup = styled.div`
  display: flex;
  flex-direction: column;
`;

export const Label = styled.label`
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.25rem;
`;

export const InputGroup = styled.div`
  position: relative;
`;

export const Input = styled.input`
  display: block;
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  transition: all 0.2s;

  &:focus {
    outline: none;
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
  }

  &::placeholder {
    color: #9ca3af;
  }
`;

export const InputIcon = styled.div`
  position: absolute;
  inset-y: 0;
  right: 0;
  padding-right: 0.75rem;
  display: flex;
  align-items: center;
`;

export const InputIconSvg = styled.svg`
  height: 1.25rem;
  width: 1.25rem;
  color: #9ca3af;
`;

export const ButtonGroup = styled.div`
  display: flex;
  flex-direction: column;
`;

export const SubmitButton = styled.button`
  width: 100%;
  display: flex;
  justify-content: center;
  padding: 0.75rem 1rem;
  border: 1px solid transparent;
  border-radius: 0.5rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  font-size: 0.875rem;
  font-weight: 600;
  color: white;
  background: #059669;
  transition: background-color 0.2s;

  &:hover {
    background: #047857;
  }

  &:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
  }

  &:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
`;

export const ButtonContent = styled.div`
  display: flex;
  align-items: center;
`;

export const Spinner = styled.svg`
  animation: spin 1s linear infinite;
  margin-left: -0.25rem;
  margin-right: 0.75rem;
  height: 1.25rem;
  width: 1.25rem;
  color: white;

  @keyframes spin {
    from {
      transform: rotate(0deg);
    }
    to {
      transform: rotate(360deg);
    }
  }
`;

export const SpinnerCircle = styled.circle`
  opacity: 0.25;
`;

export const SpinnerPath = styled.path`
  opacity: 0.75;
`;

export const Footer = styled.div`
  text-align: center;
`;

export const FooterLink = styled(Link)`
  color: #059669;
  font-weight: 500;
  transition: color 0.2s;

  &:hover {
    color: #047857;
  }
`;
