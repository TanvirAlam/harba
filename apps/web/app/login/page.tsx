"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { useAuth } from "../../contexts/AuthContext";
import {
  Container,
  Card,
  Header,
  IconContainer,
  Icon,
  Title,
  Subtitle,
  Form,
  FormGroup,
  Label,
  InputGroup,
  Input,
  InputIcon,
  InputIconSvg,
  Button,
  ButtonContent,
  Spinner,
  ErrorMessage,
  Footer,
  FooterLink,
} from "./page.styles";

export const dynamic = "force-dynamic";

export default function LoginPage() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const { login } = useAuth();
  const router = useRouter();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setLoading(true);

    try {
      await login({ username: email, password });
      router.push("/dashboard");
    } catch (err: unknown) {
      const error = err as { response?: { data?: { message?: string } } };
      setError(error.response?.data?.message || "Login failed");
    } finally {
      setLoading(false);
    }
  };

  return (
    <Container>
      <Card>
        <Header>
          <IconContainer>
            <Icon fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"
              />
            </Icon>
          </IconContainer>
          <Title>Welcome back</Title>
          <Subtitle>Sign in to your account to continue</Subtitle>
        </Header>
        <Form onSubmit={handleSubmit} method="post">
          {error && <ErrorMessage>{error}</ErrorMessage>}
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
                placeholder="Enter your email"
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
                placeholder="Enter your password"
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
          </FormGroup>
          <Button type="submit" disabled={loading}>
            <ButtonContent>
              {loading && (
                <Spinner fill="none" viewBox="0 0 24 24">
                  <circle
                    className="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    strokeWidth="4"
                  ></circle>
                  <path
                    className="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  ></path>
                </Spinner>
              )}
              {loading ? "Signing in..." : "Sign in"}
            </ButtonContent>
          </Button>
          <Footer>
              <FooterLink href="/register">
                Don&apos;t have an account? Register here
              </FooterLink>
          </Footer>
        </Form>
      </Card>
    </Container>
  );
}
