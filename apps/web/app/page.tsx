"use client";

import { useAuth } from "../contexts/AuthContext";
import {
  Container,
  InnerContainer,
  CenterContent,
  Title,
  Description,
  ButtonContainer,
  UserSection,
  WelcomeText,
  StyledLink,
  LoadingContainer,
  LoadingText,
} from "./page.styles";

export default function Home() {
  const { user, loading } = useAuth();

  if (loading) {
    return (
      <LoadingContainer>
        <LoadingText>Loading...</LoadingText>
      </LoadingContainer>
    );
  }

  return (
    <Container>
      <InnerContainer>
        <CenterContent>
          <Title>
            Welcome to the App
          </Title>
          <Description>
            A full-stack application with Symfony backend and Next.js frontend
            featuring JWT authentication.
          </Description>

          <ButtonContainer>
            {user ? (
              <UserSection>
                <WelcomeText>
                  Welcome back, {user.email}!
                </WelcomeText>
                <StyledLink href="/dashboard" $primary>
                  Go to Dashboard
                </StyledLink>
              </UserSection>
            ) : (
              <UserSection>
                <StyledLink href="/login" $primary>
                  Sign In
                </StyledLink>
                <StyledLink href="/register">
                  Create Account
                </StyledLink>
              </UserSection>
            )}
          </ButtonContainer>
        </CenterContent>
      </InnerContainer>
    </Container>
  );
}
