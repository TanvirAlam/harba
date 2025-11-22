import styled from "styled-components";
import Link from "next/link";

export const Container = styled.div`
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(to bottom right, #dbeafe, #3b82f6);
  padding: 1rem;
`;

export const Card = styled.div`
  background: white;
  border-radius: 1rem;
  box-shadow:
    0 20px 25px -5px rgba(0, 0, 0, 0.1),
    0 10px 10px -5px rgba(0, 0, 0, 0.04);
  padding: 2rem;
  border: 1px solid #e5e7eb;
  max-width: 28rem;
  width: 100%;
`;

export const Header = styled.div`
  text-align: center;
  margin-bottom: 1.5rem;
`;

export const IconContainer = styled.div`
  margin: 0 auto 1rem;
  height: 3rem;
  width: 3rem;
  background: #3b82f6;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
`;

export const Icon = styled.svg`
  height: 1rem;
  width: 1rem;
  color: white;
`;

export const Title = styled.h2`
  font-size: 1.875rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 0.5rem;
`;

export const Subtitle = styled.p`
  color: #6b7280;
`;

export const Form = styled.form`
  margin-top: 2rem;
  space-y: 1.5rem;
`;

export const FormGroup = styled.div`
  margin-bottom: 1rem;
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
  padding: 0.75rem 2.5rem 0.75rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  transition:
    border-color 0.2s,
    box-shadow 0.2s;
  font-size: 1rem;
  color: white;

  &:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  &::placeholder {
    color: #9ca3af;
  }
`;

export const InputIcon = styled.div`
  position: absolute;
  top: 50%;
  right: 0.75rem;
  transform: translateY(-50%);
  display: flex;
  align-items: center;
  pointer-events: none;
`;

export const InputIconSvg = styled.svg`
  height: 1rem;
  width: 1rem;
  color: #9ca3af;
  display: flex;
`;

export const Button = styled.button`
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
  background: #3b82f6;
  transition: background-color 0.2s;

  &:hover {
    background: #2563eb;
  }

  &:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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
  margin-right: 0.5rem;
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

export const ErrorMessage = styled.div`
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
  padding: 1rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  margin-bottom: 1rem;
`;

export const Footer = styled.div`
  text-align: center;
  margin-top: 1.5rem;
`;

export const FooterLink = styled(Link)`
  color: #3b82f6;
  font-weight: 500;
  transition: color 0.2s;

  &:hover {
    color: #2563eb;
  }
`;
