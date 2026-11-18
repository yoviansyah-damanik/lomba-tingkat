# Questionnaire & User Feedback System (Panitia & Dewan Juri)

Official evaluation and user feedback module for Committee and Judges on application usability, scoring reliability, and future feature recommendations.

## 1. Features
1. **Questionnaire Form (`/kuisioner`, `questionnaire.index`)**:
   - **Access**: Committee (`role:committee`), Judges (`role:judge`), and Admin.
   - **5-Star Rating Scales**: Overall satisfaction, UI/UX navigation, scoring & attendance features reliability, field speed/stability, and scoring transparency.
   - **Multiple Choice**: Device used, efficiency level vs manual paper, main field obstacles, most useful features, rubric clarity, and future continuity readiness.
   - **Essay / Text Recommendations**: Urgent recommended features to develop, field experience, and constructive suggestions.
   - **Update Support**: Detects existing response for current user & event, permitting updates without duplicate submissions.
2. **Questionnaire Recap (`/admin/questionnaires`, `admin.questionnaires.recap`)**:
   - **Access**: Administrator & Superadmin (`role:admin|superadmin`).
   - **Aggregate KPIs**: Total respondents, committee vs judge counts, combined average rating (out of 5.0), and positive efficiency rate.
   - **Visual Breakdown**: Aspect average progress bars and percentage distributions for multiple choices.
   - **Directory & Detail Modal**: Filterable list by role with search and full response inspection modal.
3. **Navigation & Dashboard Shortcuts**:
   - Navigation links in `Navigation.php` under "Kegiatan Saya" and "Informasi & Laporan".
   - Direct shortcut stat cards on `/dashboard` for Committee, Judges, and Administrator.

## 2. Database Schema
- Table: `questionnaire_responses`
- Key Columns: `user_id`, `event_id`, `role`, `duty_assignment`, `rating_overall`, `rating_ui_ux`, `rating_features`, `rating_speed`, `rating_transparency`, `device_used`, `efficiency_level`, `main_obstacle`, `most_useful_feature`, `rubric_clarity`, `future_readiness`, `recommended_features`, `experience_feedback`, `general_suggestions`.
- Unique constraint: `['user_id', 'event_id']`.
