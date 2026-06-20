import type { Difficulty } from "@/types/practice";

export interface ProgressChartPoint {
    date: string;
    accuracy: number;
}

export interface LearnerProgressSummary {
    total_sessions: number;
    avg_accuracy: number;
    current_level: Difficulty;
    streak_days: number;
    last_practice_at: string | null;
    suggested_level: Difficulty;
    suggested_track_name: string;
}

export interface RecentSession {
    id: number;
    track_name: string;
    completed_at: string;
    accuracy_pct: number;
    question_count: number;
}

export interface LearnerDashboardData {
    summary: LearnerProgressSummary;
    chart_points: ProgressChartPoint[];
    recent_sessions: RecentSession[];
}
