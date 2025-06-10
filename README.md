## Getting Started

First, run the development server:

## Available Commands

The project includes several useful Make commands to help you manage the application:

- `make up` - Start the project using Docker Compose
- `make stop` - Stop the project containers
- `make down` - Stop and remove all containers
- `make app-install` - Install PHP dependencies using Composer
- `make app-cc` - Clear the application cache
- `make app-ccc` - Clear all caches including cache pools
- `make app-migrate` - Run database migrations
- `make app-migration-generate` - Generate a new migration file
- `make app-connect` - Connect to the PHP container via bash

To see all available commands with their descriptions, you can run:
```bash
make help
```

