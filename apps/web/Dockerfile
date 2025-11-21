# Build stage
FROM node:20-alpine AS builder

WORKDIR /app

# Copy workspace files
COPY package.json pnpm-lock.yaml pnpm-workspace.yaml ./

# Copy apps and packages
COPY apps ./apps
COPY packages ./packages

# Install dependencies
RUN npm install -g pnpm && pnpm install --frozen-lockfile

# Build the application
WORKDIR /app/apps/web
ENV NEXT_TURBO_FORCE=false
RUN pnpm build

# Production stage
FROM node:20-alpine AS runner

WORKDIR /app

# Install next
RUN npm install next@16.0.1

# Copy built application
COPY --from=builder /app/apps/web/.next ./.next
COPY --from=builder /app/apps/web/package.json ./package.json
COPY --from=builder /app/apps/web/public ./public

# Expose port
EXPOSE 3000

# Set environment
ENV NODE_ENV=production

# Start the application
CMD ["/app/node_modules/.bin/next", "start"]