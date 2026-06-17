import type { Difficulty } from "@/types/practice";

export interface AdminKpis {
    total_learners: number;
    completed_sessions: number;
    active_sessions: number;
    active_tracks: number;
    avg_accuracy: number;
}

export interface AdminLearnerRow {
    id: number;
    name: string;
    email: string;
    sessions_completed: number;
    last_practice_at: string | null;
    level_estimated: Difficulty;
    accuracy_pct: number;
}

export interface AdminTrackRow {
    id: number;
    slug: string;
    name: string;
    description: string | null;
    difficulty: Difficulty;
    is_active: boolean;
    session_count: number;
}

export interface AdminTrackReport {
    track_id: number;
    track_name: string;
    sessions_count: number;
    avg_accuracy: number;
    active_learners: number;
}

export interface AdminDashboardData {
    kpis: AdminKpis;
    recent_learners: AdminLearnerRow[];
    track_reports: AdminTrackReport[];
}
