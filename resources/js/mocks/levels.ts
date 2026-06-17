import type {
    QuizChallenge,
    SpeakingChallenge,
    TierInfo,
} from "@/types/levels";

export const tiers: TierInfo[] = [
    {
        slug: "basico",
        name: "Nivel Básico",
        description: "Saludos, palabras cotidianas y frases cortas.",
    },
    {
        slug: "intermedio",
        name: "Nivel Intermedio",
        description: "Conversaciones del día a día y vocabulario técnico ligero.",
    },
    {
        slug: "avanzado",
        name: "Nivel Avanzado",
        description: "Explicaciones complejas y contexto profesional.",
    },
];

const speakingBasico: Omit<SpeakingChallenge, "id" | "tier">[] = [
    {
        phase: 1,
        prompt: "Hello",
        expected_translation: "Hola",
        hint: "Saludo informal universal.",
    },
    {
        phase: 2,
        prompt: "Good morning",
        expected_translation: "Buenos días",
        hint: "Saludo matutino.",
    },
    {
        phase: 3,
        prompt: "How are you?",
        expected_translation: "¿Cómo estás?",
        hint: "Pregunta sobre el estado de alguien.",
    },
    {
        phase: 4,
        prompt: "Thank you very much",
        expected_translation: "Muchas gracias",
        hint: "Agradecimiento enfatizado.",
    },
    {
        phase: 5,
        prompt: "See you tomorrow",
        expected_translation: "Nos vemos mañana",
        hint: "Despedida para el día siguiente.",
    },
];

const speakingIntermedio: Omit<SpeakingChallenge, "id" | "tier">[] = [
    {
        phase: 1,
        prompt: "I am working on a new feature",
        expected_translation: "Estoy trabajando en una nueva funcionalidad",
        hint: "Presente continuo en contexto laboral.",
    },
    {
        phase: 2,
        prompt: "Can you review my pull request?",
        expected_translation: "¿Puedes revisar mi pull request?",
        hint: "Petición educada en equipo de desarrollo.",
    },
    {
        phase: 3,
        prompt: "The deployment failed last night",
        expected_translation: "El despliegue falló anoche",
        hint: "Reportar un incidente.",
    },
    {
        phase: 4,
        prompt: "We need to fix this bug before the release",
        expected_translation: "Necesitamos arreglar este bug antes del lanzamiento",
        hint: "Urgencia técnica.",
    },
    {
        phase: 5,
        prompt: "Let me schedule a meeting with the team",
        expected_translation: "Déjame agendar una reunión con el equipo",
        hint: "Coordinación de equipo.",
    },
];

const speakingAvanzado: Omit<SpeakingChallenge, "id" | "tier">[] = [
    {
        phase: 1,
        prompt: "The API latency increased after the last deployment",
        expected_translation: "La latencia de la API aumentó después del último despliegue",
        hint: "Diagnóstico de rendimiento.",
    },
    {
        phase: 2,
        prompt: "We should refactor this module to improve maintainability",
        expected_translation: "Deberíamos refactorizar este módulo para mejorar la mantenibilidad",
        hint: "Propuesta técnica formal.",
    },
    {
        phase: 3,
        prompt: "The root cause was a race condition in the cache layer",
        expected_translation: "La causa raíz fue una condición de carrera en la capa de caché",
        hint: "Postmortem técnico.",
    },
    {
        phase: 4,
        prompt: "I recommend splitting the monolith into smaller services",
        expected_translation: "Recomiendo dividir el monolito en servicios más pequeños",
        hint: "Arquitectura de software.",
    },
    {
        phase: 5,
        prompt: "Our observability stack helped us detect the issue early",
        expected_translation: "Nuestro stack de observabilidad nos ayudó a detectar el problema a tiempo",
        hint: "DevOps y monitoreo.",
    },
];

function buildSpeaking(
    tier: SpeakingChallenge["tier"],
    items: Omit<SpeakingChallenge, "id" | "tier">[],
    idOffset: number,
): SpeakingChallenge[] {
    return items.map((item, index) => ({
        id: idOffset + index + 1,
        tier,
        ...item,
    }));
}

export const speakingChallenges: SpeakingChallenge[] = [
    ...buildSpeaking("basico", speakingBasico, 0),
    ...buildSpeaking("intermedio", speakingIntermedio, 5),
    ...buildSpeaking("avanzado", speakingAvanzado, 10),
];

const quizBasico: Omit<QuizChallenge, "id" | "tier">[] = [
    {
        phase: 1,
        prompt: "Apple",
        options: ["Manzana", "Pera", "Uva"],
        correct_index: 0,
    },
    {
        phase: 2,
        prompt: "Book",
        options: ["Libro", "Mesa", "Silla"],
        correct_index: 0,
    },
    {
        phase: 3,
        prompt: "I need water",
        options: ["Necesito agua", "Tengo sed de café", "Quiero pan"],
        correct_index: 0,
    },
    {
        phase: 4,
        prompt: "Good night",
        options: ["Buenas noches", "Buenos días", "Hasta luego"],
        correct_index: 0,
    },
    {
        phase: 5,
        prompt: "My name is Alex",
        options: ["Me llamo Alex", "Vivo en Alex", "Soy de noche"],
        correct_index: 0,
    },
];

const quizIntermedio: Omit<QuizChallenge, "id" | "tier">[] = [
    {
        phase: 1,
        prompt: "Merge conflict",
        options: ["Conflicto de fusión", "Error de red", "Código duplicado"],
        correct_index: 0,
    },
    {
        phase: 2,
        prompt: "The server is down",
        options: ["El servidor está caído", "El servidor es rápido", "El servidor es nuevo"],
        correct_index: 0,
    },
    {
        phase: 3,
        prompt: "We rolled back the release",
        options: ["Revertimos el lanzamiento", "Aceleramos el lanzamiento", "Cancelamos el equipo"],
        correct_index: 0,
    },
    {
        phase: 4,
        prompt: "Write unit tests",
        options: ["Escribir pruebas unitarias", "Borrar la base de datos", "Cerrar el proyecto"],
        correct_index: 0,
    },
    {
        phase: 5,
        prompt: "The build passed successfully",
        options: ["La compilación pasó exitosamente", "La compilación falló ayer", "La compilación es lenta"],
        correct_index: 0,
    },
];

const quizAvanzado: Omit<QuizChallenge, "id" | "tier">[] = [
    {
        phase: 1,
        prompt: "Idempotent operation",
        options: ["Operación idempotente", "Operación aleatoria", "Operación bloqueada"],
        correct_index: 0,
    },
    {
        phase: 2,
        prompt: "Eventual consistency",
        options: ["Consistencia eventual", "Consistencia inmediata", "Consistencia imposible"],
        correct_index: 0,
    },
    {
        phase: 3,
        prompt: "Horizontal scaling",
        options: ["Escalado horizontal", "Escalado vertical único", "Reducción de nodos"],
        correct_index: 0,
    },
    {
        phase: 4,
        prompt: "Circuit breaker pattern",
        options: ["Patrón circuit breaker", "Patrón singleton", "Patrón decorador"],
        correct_index: 0,
    },
    {
        phase: 5,
        prompt: "Zero downtime deployment",
        options: ["Despliegue sin tiempo de inactividad", "Despliegue manual lento", "Despliegue sin pruebas"],
        correct_index: 0,
    },
];

function buildQuiz(
    tier: QuizChallenge["tier"],
    items: Omit<QuizChallenge, "id" | "tier">[],
    idOffset: number,
): QuizChallenge[] {
    return items.map((item, index) => ({
        id: idOffset + index + 1,
        tier,
        ...item,
    }));
}

export const quizChallenges: QuizChallenge[] = [
    ...buildQuiz("basico", quizBasico, 0),
    ...buildQuiz("intermedio", quizIntermedio, 5),
    ...buildQuiz("avanzado", quizAvanzado, 10),
];
