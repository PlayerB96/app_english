import type { LearningTrack } from "@/types/practice";

/** Referencia legacy; el listado admin sigue en MockData.php. */
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
];
