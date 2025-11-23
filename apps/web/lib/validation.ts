/**
 * Validation utility functions for form inputs
 */

export interface ValidationResult {
  valid: boolean;
  error?: string;
}

/**
 * Validates email format
 */
export function validateEmail(email: string): ValidationResult {
  if (!email) {
    return { valid: false, error: "Email is required" };
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    return { valid: false, error: "Invalid email format" };
  }

  return { valid: true };
}

/**
 * Validates password strength
 * Requirements: At least 8 characters, contains uppercase, lowercase, and number
 */
export function validatePassword(password: string): ValidationResult {
  if (!password) {
    return { valid: false, error: "Password is required" };
  }

  if (password.length < 8) {
    return { valid: false, error: "Password must be at least 8 characters" };
  }

  if (!/[A-Z]/.test(password)) {
    return {
      valid: false,
      error: "Password must contain at least one uppercase letter",
    };
  }

  if (!/[a-z]/.test(password)) {
    return {
      valid: false,
      error: "Password must contain at least one lowercase letter",
    };
  }

  if (!/[0-9]/.test(password)) {
    return { valid: false, error: "Password must contain at least one number" };
  }

  return { valid: true };
}

/**
 * Validates password confirmation matches
 */
export function validatePasswordMatch(
  password: string,
  confirmPassword: string
): ValidationResult {
  if (password !== confirmPassword) {
    return { valid: false, error: "Passwords do not match" };
  }

  return { valid: true };
}

