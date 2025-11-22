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

/**
 * Validates required field
 */
export function validateRequired(
  value: string | number | null | undefined,
  fieldName: string
): ValidationResult {
  if (value === null || value === undefined || value === "") {
    return { valid: false, error: `${fieldName} is required` };
  }

  return { valid: true };
}

/**
 * Validates datetime is not in the past
 */
export function validateFutureDate(datetime: string): ValidationResult {
  const selectedDate = new Date(datetime);
  const now = new Date();

  if (selectedDate < now) {
    return { valid: false, error: "Date and time cannot be in the past" };
  }

  return { valid: true };
}

/**
 * Validates datetime is within reasonable future range (6 months)
 */
export function validateReasonableFuture(datetime: string): ValidationResult {
  const selectedDate = new Date(datetime);
  const maxFutureDate = new Date();
  maxFutureDate.setMonth(maxFutureDate.getMonth() + 6);

  if (selectedDate > maxFutureDate) {
    return {
      valid: false,
      error: "Date cannot be more than 6 months in the future",
    };
  }

  return { valid: true };
}
