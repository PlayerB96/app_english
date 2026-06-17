import type { LearningTrack, MockQuestion } from "@/types/practice";

export const learningTracks: LearningTrack[] = [
    {
        id: 1,
        slug: "dev-vocabulary",
        name: "Dev Vocabulary",
        description:
            "Términos técnicos en inglés para el día a día del desarrollo.",
        difficulty: "beginner",
        is_active: true,
        sort_order: 1,
        session_count: 42,
    },
    {
        id: 2,
        slug: "technical-interviews",
        name: "Technical Interviews",
        description:
            "Respuestas claras para entrevistas técnicas en inglés.",
        difficulty: "intermediate",
        is_active: true,
        sort_order: 2,
        session_count: 28,
    },
    {
        id: 3,
        slug: "documentation",
        name: "Documentation",
        description:
            "Redacción de READMEs, ADRs y comentarios profesionales.",
        difficulty: "advanced",
        is_active: true,
        sort_order: 3,
        session_count: 15,
    },
];

export const questionsByTrack: Record<string, MockQuestion[]> = {
    "1": [
        {
            id: 101,
            practice_session_id: null,
            learning_track_id: 1,
            prompt: 'Explain what a "pull request" is in your own words.',
            context:
                "Imagine you are onboarding a junior developer on your team.",
            difficulty: "beginner",
            source: "ai",
            sample_answer:
                "A pull request is a way to propose changes to a codebase and request a review before merging.",
        },
    ],
};
