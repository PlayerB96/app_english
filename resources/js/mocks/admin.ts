import type {
    AdminDashboardData,
    AdminLearnerRow,
    AdminTrackReport,
} from "@/types/admin";

export const adminLearners: AdminLearnerRow[] = [
    {
        id: 2,
        name: "Alex Developer",
        email: "alex@example.com",
        sessions_completed: 14,
        last_practice_at: "2026-06-13T16:00:00+00:00",
        level_estimated: "intermediate",
        accuracy_pct: 82,
    },
];

export const adminTrackReports: AdminTrackReport[] = [
    {
        track_id: 1,
        track_name: "Dev Vocabulary",
        sessions_count: 42,
        avg_accuracy: 76.8,
        active_learners: 18,
    },
];

export const adminDashboard: AdminDashboardData = {
    kpis: {
        total_learners: 24,
        completed_sessions: 156,
        active_sessions: 3,
        active_tracks: 3,
        avg_accuracy: 74.2,
    },
    recent_learners: adminLearners,
    track_reports: adminTrackReports,
};
