# AI Personal Learning Assistant

A comprehensive web-based learning management system powered by AI, designed to help students organize their studies, get instant doubt resolution, and track their progress through adaptive quizzes.

## Features

### User Registration & Authentication
- Secure user registration with role selection (Student, Teacher, Administrator)
- Profile picture upload support
- Institution/School name tracking
- Secure password hashing

### Personalized Study Planner
- Create custom study schedules with specific time slots
- Set learning goals and deadlines
- Subject-wise time allocation
- Calendar integration with visual schedule
- Progress tracking and adjustment
- Goal achievement tracking

### AI-Powered Doubt Resolution
- Natural language query processing
- Instant response generation using multiple AI APIs
- Contextual explanations and examples
- Query history and bookmarking
- Multi-subject support
- Fallback API system for reliability

### Adaptive Quiz Generator
- Dynamic quiz generation based on topics
- Multiple question types (MCQ, True/False, Short Answer)
- Difficulty level adaptation (Easy, Medium, Hard)
- Instant grading and feedback
- Performance analytics
- Detailed result analysis
- Retry options for improvement

### Progress Analytics
- Study hours tracking
- Quiz performance monitoring
- Subject-wise performance breakdown
- Streak tracking
- Average score calculation
- Visual progress indicators

### Additional Features
- Dark/Light theme toggle
- Responsive design for all devices
- Beautiful blue and white color scheme
- Professional UI with Bootstrap 5
- Real-time notifications
- Learning resources section

## Installation

### Prerequisites
- Laragon installed at C:/laragon
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser

### Setup Instructions

1. Extract the project to your Laragon www directory:
   ```
   C:/laragon/www/ai_learning_assistant/
   ```

2. Start Laragon and ensure Apache and MySQL are running

3. The database will be created automatically on first run

4. Access the application:
   ```
   http://localhost/ai_learning_assistant/
   ```

5. Register a new account and start learning!

## AI API Configuration

The system uses multiple free AI APIs with automatic fallback:

1. **Hugging Face** (No API key required for basic models)
2. **OpenRouter** (Free tier available)
3. **Together AI** (Free tier available)

To configure API keys (optional for better performance):
- Edit `config/ai_config.php`
- Add your API keys to the respective configuration

## Project Structure

```
ai_learning_assistant/
├── api/                    # API endpoints
│   ├── add_goal.php
│   ├── add_plan.php
│   ├── chat.php
│   ├── clear_chat.php
│   ├── create_quiz.php
│   └── submit_quiz.php
├── assets/                 # Static assets
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── theme.js
├── auth/                   # Authentication
│   ├── login.php
│   ├── logout.php
│   └── register.php
├── config/                 # Configuration files
│   ├── ai_config.php
│   ├── config.php
│   └── database.php
├── dashboard/              # Main application pages
│   ├── chat.php
│   ├── index.php
│   ├── planner.php
│   ├── progress.php
│   ├── quizzes.php
│   ├── quiz_result.php
│   ├── resources.php
│   ├── settings.php
│   └── take_quiz.php
├── includes/               # Reusable components
│   └── sidebar.php
├── uploads/                # User uploads
│   └── profiles/
├── .htaccess
├── index.php
└── README.md
```

## Database Schema

The application automatically creates the following tables:
- users
- study_plans
- study_goals
- chat_history
- quizzes
- quiz_questions
- notifications
- user_progress

## Security Features

- Password hashing using PHP password_hash()
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- Session-based authentication
- Secure file upload handling

## Browser Support

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Opera

## Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework**: Bootstrap 5
- **Icons**: Bootstrap Icons
- **AI Integration**: Multiple free AI APIs

## Default Credentials

After registration, you can create your own account with any role:
- Student
- Teacher
- Administrator

## Support

For issues or questions, please check the code comments or review the implementation details in the respective files.

## License

This project is created for educational purposes.
