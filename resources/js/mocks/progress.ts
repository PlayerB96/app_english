import type { LearnerDashboardData } from "@/types/progress";

export const learnerDashboard: LearnerDashboardData = {
    summary: {
        total_sessions: 12,
        avg_accuracy: 78.5,
        current_level: "intermediate",
        streak_days: 5,
        last_practice_at: "2026-06-13T18:30:00+00:00",
        suggested_level: "intermediate",
    },
    chart_points: [
        { date: "2026-06-08", accuracy: 62 },
        { date: "2026-06-09", accuracy: 68 },
        { date: "2026-06-10", accuracy: 71 },
        { date: "2026-06-11", accuracy: 75 },
        { date: "2026-06-12", accuracy: 80 },
        { date: "2026-06-13", accuracy: 78 },
    ],
    recent_sessions: [],
};
