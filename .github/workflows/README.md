# GitHub Actions Workflows

This project uses GitHub Actions for automated testing of both backend and frontend applications.

## Workflows

### Backend Tests (`backend-test.yml`)
- **Trigger**: Push/PR affecting `apps/api-php/**`
- **Environment**: Ubuntu with MySQL 8.0
- **Steps**:
  - Setup PHP 8.2 with Composer
  - Install dependencies
  - Setup test database
  - Run migrations
  - Execute PHPUnit tests with coverage

### Frontend Tests (`frontend-test.yml`)
- **Trigger**: Push/PR affecting `apps/web/**`
- **Environment**: Ubuntu with Node.js 20
- **Steps**:
  - Setup pnpm and Node.js
  - Install dependencies
  - Type checking
  - Linting
  - Run Jest tests with coverage
  - Upload coverage to Codecov

## Testing Workflows Locally

### Prerequisites
1. Install GitHub CLI: https://cli.github.com/
2. Authenticate: `gh auth login`
3. Install act (for local workflow testing): https://github.com/nektos/act

### Running Workflows Locally

```bash
# Test backend workflow
act -j test -W .github/workflows/backend-test.yml

# Test frontend workflow
act -j test -W .github/workflows/frontend-test.yml

# Or use GitHub CLI to trigger remote workflows
gh workflow run backend-test.yml
gh workflow run frontend-test.yml
```

### Viewing Workflow Results

```bash
# List recent workflow runs
gh run list

# View specific run details
gh run view <run-id>

# View logs
gh run view <run-id> --log
```

## Workflow Configuration

- **Backend**: Uses MySQL service container for database testing
- **Frontend**: Uses pnpm for dependency management
- **Triggers**: Path-based filtering to run only relevant tests
- **Coverage**: Both workflows generate and upload coverage reports