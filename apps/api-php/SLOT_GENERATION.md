# Slot Generation Implementation

## Overview
The available slots generation feature has been fully implemented in `BookingController::generateAvailableSlots()`.

## Features Implemented

### ✅ 30-Day Range
- Generates slots starting from today
- Covers the next 30 days

### ✅ Respects Provider Working Hours
- Reads working hours from the Provider entity's JSON field
- Format: `{"Monday": "09:00-17:00", "Tuesday": "09:00-17:00", ...}`
- Only generates slots on days when the provider works
- Slots are within the specified start and end times

### ✅ 30-Minute Intervals
- Creates slots every 30 minutes
- Examples: 09:00, 09:30, 10:00, 10:30, etc.

### ✅ Service Duration Consideration
- Checks if the service duration fits within the remaining working hours
- Only adds a slot if: `slot_start_time + service_duration <= working_hours_end_time`
- Prevents booking a 60-minute service at 16:30 if provider ends at 17:00

### ✅ Filters Existing Bookings
- Queries all existing bookings for the provider in the date range
- Removes already-booked slots from available slots
- Uses efficient lookup with associative array

## Algorithm Details

```
For each day in next 30 days:
  1. Get day of week (Monday, Tuesday, etc.)
  2. Check if provider works on this day
  3. Parse working hours (e.g., "09:00-17:00")
  4. Generate 30-minute slots from start to end time:
     a. Create slot time
     b. Check if service fits (slot + duration <= end time)
     c. Check if slot is not already booked
     d. If both checks pass, add to available slots
     e. Move to next 30-minute interval
```

## Example Response

```json
[
  "2025-11-22 09:00:00",
  "2025-11-22 09:30:00",
  "2025-11-22 10:00:00",
  "2025-11-22 10:30:00",
  ...
]
```

## API Endpoint

**GET** `/api/bookings/available-slots?provider_id={id}&service_id={id}`

**Query Parameters:**
- `provider_id` (required): ID of the provider
- `service_id` (required): ID of the service

**Authentication:** Required (JWT token)

## Testing

Test cases have been created in `tests/Controller/BookingControllerSlotTest.php`:

1. **testAvailableSlotsGeneratesCorrectly**: Verifies slots are generated and booked slots are filtered out
2. **testAvailableSlotsRespectsWorkingHours**: Verifies slots respect provider working hours

Run tests with:
```bash
./vendor/bin/phpunit tests/Controller/BookingControllerSlotTest.php
```

## Performance Notes

- The query fetches all bookings for a provider in the 30-day range in one database query
- Uses an associative array for O(1) lookup when checking if a slot is booked
- Time complexity: O(D × S) where D = days and S = slots per day (typically ~16 slots for 8-hour workday)
- Space complexity: O(B + S) where B = existing bookings and S = total slots generated

## Edge Cases Handled

1. **Provider doesn't work on certain days**: Skips those days entirely
2. **Service duration longer than remaining time**: Slot not added
3. **Invalid working hours format**: Regex validation, skips invalid entries
4. **Concurrent bookings**: Database unique constraint prevents race conditions
5. **Weekend/holiday handling**: Depends on provider's working hours configuration
