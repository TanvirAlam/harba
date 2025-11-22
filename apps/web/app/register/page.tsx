"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { useAuth } from "../../contexts/AuthContext";
import {
  validateEmail,
  validatePassword,
  validatePasswordMatch,
} from "../../lib/validation";
import {
  Container,
  Content,
  Card,
  Header,
  IconContainer,
  Icon,
  Title,
  Subtitle,
  Form,
  ErrorAlert,
  SuccessAlert,
  FormFields,
  FormGroup,
  Label,
  InputGroup,
  Input,
  InputIcon,
  InputIconSvg,
  ButtonGroup,
  SubmitButton,
  ButtonContent,
  Spinner,
  SpinnerCircle,
  SpinnerPath,
  Footer,
  FooterLink,
} from "./page.styles";

export default function RegisterPage() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const [loading, setLoading] = useState(false);
  const [fieldErrors, setFieldErrors] = useState<{
    email?: string;
    password?: string;
    confirmPassword?: string;
  }>({});
  const { register } = useAuth();
  const router = useRouter();

  const validateForm = (): boolean => {
    const errors: typeof fieldErrors = {};

    // Validate email
    const emailValidation = validateEmail(email);
    if (!emailValidation.valid) {
      errors.email = emailValidation.error;
    }

    // Validate password
    const passwordValidation = validatePassword(password);
    if (!passwordValidation.valid) {
      errors.password = passwordValidation.error;
    }

    // Validate password match
    const matchValidation = validatePasswordMatch(password, confirmPassword);
    if (!matchValidation.valid) {
      errors.confirmPassword = matchValidation.error;
    }

    setFieldErrors(errors);
    return Object.keys(errors).length === 0;
  };

  const handleEmailBlur = () => {
    const validation = validateEmail(email);
    if (!validation.valid) {
      setFieldErrors((prev) => ({ ...prev, email: validation.error }));
    } else {
      setFieldErrors((prev) => ({ ...prev, email: undefined }));
    }
  };

  const handlePasswordBlur = () => {
    const validation = validatePassword(password);
    if (!validation.valid) {
      setFieldErrors((prev) => ({ ...prev, password: validation.error }));
    } else {
      setFieldErrors((prev) => ({ ...prev, password: undefined }));
    }
  };

  const handleConfirmPasswordBlur = () => {
    const validation = validatePasswordMatch(password, confirmPassword);
    if (!validation.valid) {
      setFieldErrors((prev) => ({
        ...prev,
        confirmPassword: validation.error,
      }));
    } else {
      setFieldErrors((prev) => ({ ...prev, confirmPassword: undefined }));
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setSuccess("");

    if (!validateForm()) {
      return;
    }

    setLoading(true);

    try {
      await register({ email, password });
      setSuccess("Registration successful! Please log in.");
      setTimeout(() => router.push("/login"), 2000);
    } catch (err: unknown) {
      const error = err as { response?: { data?: { message?: string } } };
      setError(error.response?.data?.message || "Registration failed");
    } finally {
      setLoading(false);
    }
  };

  return (
    <Container>
      <Content>
        <Card>
          <Header>
            <IconContainer>
              <Icon fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                />
              </Icon>
            </IconContainer>
            <Title>Create your account</Title>
            <Subtitle>Join us and start booking services</Subtitle>
          </Header>
          <Form onSubmit={handleSubmit}>
            {error && <ErrorAlert>{error}</ErrorAlert>}
            {success && <SuccessAlert>{success}</SuccessAlert>}
            <FormFields>
              <FormGroup>
                <Label htmlFor="email">Email address</Label>
                <InputGroup>
                  <Input
                    id="email"
                    name="email"
                    type="email"
                    required
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    onBlur={handleEmailBlur}
                    placeholder="Enter your email"
                    style={{
                      borderColor: fieldErrors.email ? "#ef4444" : undefined,
                    }}
                  />
                  <InputIcon>
                    <InputIconSvg
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"
                      />
                    </InputIconSvg>
                  </InputIcon>
                </InputGroup>
                {fieldErrors.email && (
                  <div style={{ color: "#ef4444", fontSize: "0.875rem", marginTop: "0.25rem" }}>
                    {fieldErrors.email}
                  </div>
                )}
              </FormGroup>
              <FormGroup>
                <Label htmlFor="password">Password</Label>
                <InputGroup>
                  <Input
                    id="password"
                    name="password"
                    type="password"
                    required
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    onBlur={handlePasswordBlur}
                    placeholder="Create a password"
                    style={{
                      borderColor: fieldErrors.password ? "#ef4444" : undefined,
                    }}
                  />
                  <InputIcon>
                    <InputIconSvg
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                      />
                    </InputIconSvg>
                  </InputIcon>
                </InputGroup>
                {fieldErrors.password && (
                  <div style={{ color: "#ef4444", fontSize: "0.875rem", marginTop: "0.25rem" }}>
                    {fieldErrors.password}
                  </div>
                )}
              </FormGroup>
              <FormGroup>
                <Label htmlFor="confirmPassword">Confirm Password</Label>
                <InputGroup>
                  <Input
                    id="confirmPassword"
                    name="confirmPassword"
                    type="password"
                    required
                    value={confirmPassword}
                    onChange={(e) => setConfirmPassword(e.target.value)}
                    onBlur={handleConfirmPasswordBlur}
                    placeholder="Confirm your password"
                    style={{
                      borderColor: fieldErrors.confirmPassword
                        ? "#ef4444"
                        : undefined,
                    }}
                  />
                  <InputIcon>
                    <InputIconSvg
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                      />
                    </InputIconSvg>
                  </InputIcon>
                </InputGroup>
                {fieldErrors.confirmPassword && (
                  <div style={{ color: "#ef4444", fontSize: "0.875rem", marginTop: "0.25rem" }}>
                    {fieldErrors.confirmPassword}
                  </div>
                )}
              </FormGroup>
            </FormFields>
            <ButtonGroup>
              <SubmitButton type="submit" disabled={loading}>
                <ButtonContent>
                  {loading && (
                    <Spinner fill="none" viewBox="0 0 24 24">
                      <SpinnerCircle
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        strokeWidth="4"
                      />
                      <SpinnerPath
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                      />
                    </Spinner>
                  )}
                  {loading ? "Creating account..." : "Create account"}
                </ButtonContent>
              </SubmitButton>
            </ButtonGroup>
            <Footer>
              <FooterLink href="/login">
                Already have an account? Sign in here
              </FooterLink>
            </Footer>
          </Form>
        </Card>
      </Content>
    </Container>
  );
}
