<?php

namespace Database\Seeders;

use App\Models\WorldQuestion;
use Illuminate\Database\Seeder;

class WorldContentSeeder extends Seeder
{
    public function run(): void
    {
        WorldQuestion::query()->delete();

        $zoneMap = $this->zoneMap();

        foreach (config('world.levels') as $level) {
            $levelId = (int) $level['id'];
            $zone = $zoneMap[$level['zone']] ?? null;

            foreach ($this->questionsForLevel($level, $zone) as $question) {
                WorldQuestion::create([
                    'world_level_id' => $levelId,
                    ...$question,
                ]);
            }
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function zoneMap(): array
    {
        $map = [];

        foreach (config('world.worlds') as $world) {
            foreach ($world['zones'] ?? [] as $zone) {
                $map[$zone['slug']] = $zone;
            }
        }

        return $map;
    }

    /**
     * @param  array<string, mixed>  $level
     * @param  array<string, mixed>|null  $zone
     * @return list<array<string, mixed>>
     */
    private function questionsForLevel(array $level, ?array $zone): array
    {
        $levelId = (int) $level['id'];
        $context = $level['scenario'];
        $gameplay = $level['gameplay'] ?? '';
        $commands = $zone['commands'] ?? [];
        $english = $zone['english'] ?? [];

        if ($levelId === 18) {
            return $this->bossQuestions($level);
        }

        $phase = ((int) $level['phase']) - 1;
        $englishWord = $english[$phase % count($english)] ?? 'file';

        $q1 = $this->translationQuestion($englishWord, $context, $levelId);
        $q2 = ($levelId % 2 === 1)
            ? $this->sentenceCompletionQuestion($levelId, $commands, $english, $context, $gameplay)
            : $this->commandContextQuestion($levelId, $commands, $context, $gameplay);
        $q3 = ($levelId % 2 === 1)
            ? $this->termMeaningQuestion($levelId, $english, $commands, $context)
            : $this->scenarioQuestion($levelId, $commands, $context, $gameplay);

        return [$q1, $q2, $q3];
    }

    /**
     * @return array<string, mixed>
     */
    private function translationQuestion(string $word, string $context, int $levelId): array
    {
        $bank = self::TRANSLATIONS[$word] ?? [
            'prompt' => "Which English word best matches the Linux term \"{$word}\"?",
            'options' => [$word, 'command', 'terminal'],
            'correct_index' => 0,
        ];

        return [
            'question_index' => 1,
            'type' => 'translation',
            'difficulty' => 'facil',
            'prompt' => $bank['prompt'],
            'context' => $context,
            'options' => $bank['options'],
            'correct_index' => $bank['correct_index'],
        ];
    }

    /**
     * @param  list<string>  $commands
     * @param  list<string>  $english
     * @return array<string, mixed>
     */
    private function sentenceCompletionQuestion(
        int $levelId,
        array $commands,
        array $english,
        string $context,
        string $gameplay,
    ): array {
        $bank = self::SENTENCE_COMPLETION[$levelId];

        return [
            'question_index' => 2,
            'type' => 'sentence_completion',
            'difficulty' => 'medio',
            'prompt' => $bank['prompt'],
            'context' => $gameplay !== '' ? "{$context} {$gameplay}" : $context,
            'options' => $bank['options'],
            'correct_index' => $bank['correct_index'],
        ];
    }

    /**
     * @param  list<string>  $commands
     * @return array<string, mixed>
     */
    private function commandContextQuestion(
        int $levelId,
        array $commands,
        string $context,
        string $gameplay,
    ): array {
        $bank = self::COMMAND_CONTEXT[$levelId];

        return [
            'question_index' => 2,
            'type' => 'command_context',
            'difficulty' => 'medio',
            'prompt' => $bank['prompt'],
            'context' => $gameplay !== '' ? "{$context} {$gameplay}" : $context,
            'options' => $bank['options'],
            'correct_index' => $bank['correct_index'],
        ];
    }

    /**
     * @param  list<string>  $english
     * @param  list<string>  $commands
     * @return array<string, mixed>
     */
    private function termMeaningQuestion(
        int $levelId,
        array $english,
        array $commands,
        string $context,
    ): array {
        $bank = self::TERM_MEANING[$levelId];

        return [
            'question_index' => 3,
            'type' => 'term_meaning',
            'difficulty' => 'dificil',
            'prompt' => $bank['prompt'],
            'context' => $context,
            'options' => $bank['options'],
            'correct_index' => $bank['correct_index'],
        ];
    }

    /**
     * @param  list<string>  $commands
     * @return array<string, mixed>
     */
    private function scenarioQuestion(
        int $levelId,
        array $commands,
        string $context,
        string $gameplay,
    ): array {
        $bank = self::SCENARIO[$levelId];

        return [
            'question_index' => 3,
            'type' => 'scenario',
            'difficulty' => 'dificil',
            'prompt' => $bank['prompt'],
            'context' => $context,
            'options' => $bank['options'],
            'correct_index' => $bank['correct_index'],
        ];
    }

    /**
     * @param  array<string, mixed>  $level
     * @return list<array<string, mixed>>
     */
    private function bossQuestions(array $level): array
    {
        $context = $level['scenario'];

        return [
            [
                'question_index' => 1,
                'type' => 'scenario',
                'difficulty' => 'dificil',
                'prompt' => 'The interviewer asks: "A user cannot read a config file. Walk me through your first three terminal checks." What is the best opening response?',
                'context' => $context,
                'options' => [
                    'Check file permissions with ls -l, verify the owner/group, and confirm the user\'s identity with whoami.',
                    'Reinstall the operating system and hope the file appears again.',
                    'Delete the file and tell the user to recreate it from memory.',
                ],
                'correct_index' => 0,
            ],
            [
                'question_index' => 2,
                'type' => 'scenario',
                'difficulty' => 'dificil',
                'prompt' => 'Interview follow-up: "Production CPU is at 100%. How do you find the culprit process?" Which approach shows sysadmin maturity?',
                'context' => $context,
                'options' => [
                    'Use top or ps to identify high CPU processes, note the PID, then investigate before killing anything.',
                    'Run kill -9 on every process until CPU drops to zero.',
                    'Ignore it — high CPU always means the server is healthy and busy.',
                ],
                'correct_index' => 0,
            ],
            [
                'question_index' => 3,
                'type' => 'scenario',
                'difficulty' => 'dificil',
                'prompt' => 'Final question: "When is sudo appropriate, and how do you explain that to a junior dev in English?" Pick the strongest answer.',
                'context' => $context,
                'options' => [
                    'Use sudo only when a task requires elevated privileges; explain that it grants temporary root access and must be used carefully.',
                    'Use sudo for every command so you never have to learn permissions.',
                    'Avoid sudo completely — real sysadmins never need elevated privileges.',
                ],
                'correct_index' => 0,
            ],
        ];
    }

    /** @var array<string, array{prompt: string, options: list<string>, correct_index: int}> */
    private const TRANSLATIONS = [
        'file' => [
            'prompt' => 'The villager mentions an "archivo" on the server. Which is the correct English word?',
            'options' => ['file', 'folder', 'process'],
            'correct_index' => 0,
        ],
        'folder' => [
            'prompt' => 'An NPC says "Open the folder". Which English word means "carpeta / directorio"?',
            'options' => ['folder', 'file', 'permission'],
            'correct_index' => 0,
        ],
        'create' => [
            'prompt' => 'The elder asks you to "create" a new path. Which English verb is "crear"?',
            'options' => ['create', 'delete', 'copy'],
            'correct_index' => 0,
        ],
        'copy' => [
            'prompt' => 'You must "copy" the old map. Which English verb means "copiar"?',
            'options' => ['copy', 'move', 'search'],
            'correct_index' => 0,
        ],
        'move' => [
            'prompt' => 'You need to "move" files between directories. Which English verb is "mover"?',
            'options' => ['move', 'delete', 'list'],
            'correct_index' => 0,
        ],
        'delete' => [
            'prompt' => 'You must "delete" corrupted branches. Which English verb means "eliminar"?',
            'options' => ['delete', 'rename', 'list'],
            'correct_index' => 0,
        ],
        'search' => [
            'prompt' => 'You must "search" subdirectories for a hidden trail. Which English verb is "buscar"?',
            'options' => ['search', 'create', 'deny'],
            'correct_index' => 0,
        ],
        'permission' => [
            'prompt' => 'A gate shows a "permission" error. Which English word means "permiso"?',
            'options' => ['permission', 'owner', 'process'],
            'correct_index' => 0,
        ],
        'owner' => [
            'prompt' => 'The guardian asks "Who is the owner?" Which English word means "propietario"?',
            'options' => ['owner', 'group', 'command'],
            'correct_index' => 0,
        ],
        'group' => [
            'prompt' => 'You configure "group" access for your party. Which English word means "grupo (de usuarios)"?',
            'options' => ['group', 'hidden file', 'CPU usage'],
            'correct_index' => 0,
        ],
        'access' => [
            'prompt' => 'The locked door mentions "access". Which English word means "acceso"?',
            'options' => ['access', 'memory', 'root directory'],
            'correct_index' => 0,
        ],
        'denied' => [
            'prompt' => 'The system says "Access denied". Which English word means "denegado / rechazado"?',
            'options' => ['denied', 'approved', 'running'],
            'correct_index' => 0,
        ],
        'process' => [
            'prompt' => 'A "process" is blocking the mine tracks. Which English word means "proceso (programa en ejecución)"?',
            'options' => ['process', 'system folder', 'write permission'],
            'correct_index' => 0,
        ],
        'running' => [
            'prompt' => 'You identify services that are "running". Which English word means "en ejecución / activo"?',
            'options' => ['running', 'stopped permanently', 'without permissions'],
            'correct_index' => 0,
        ],
        'memory' => [
            'prompt' => 'There is a "memory" leak in the mine. Which English word refers to RAM?',
            'options' => ['memory', 'log file', 'absolute path'],
            'correct_index' => 0,
        ],
        'crash' => [
            'prompt' => 'You prevent a server "crash". Which English word means "caída / colapso del sistema"?',
            'options' => ['crash', 'successful backup', 'scheduled reboot'],
            'correct_index' => 0,
        ],
        'usage' => [
            'prompt' => 'You monitor CPU "usage" before restarting the drill. Which English word means "uso / consumo"?',
            'options' => ['usage', 'username', 'delete command'],
            'correct_index' => 0,
        ],
        'open' => [
            'prompt' => 'A villager asks you to "open" a directory. Which English verb means "abrir / acceder a"?',
            'options' => ['open', 'delete', 'hide'],
            'correct_index' => 0,
        ],
        'path' => [
            'prompt' => 'You build the festival "path". Which English word means "ruta (de archivo o directorio)"?',
            'options' => ['path', 'special permission', 'zombie process'],
            'correct_index' => 0,
        ],
        'directory' => [
            'prompt' => 'You work inside a "directory" in the forest. Which English word means "directorio / carpeta"?',
            'options' => ['directory', 'kernel process', 'kill signal'],
            'correct_index' => 0,
        ],
    ];

    /** @var array<int, array{prompt: string, options: list<string>, correct_index: int}> */
    private const SENTENCE_COMPLETION = [
        1 => [
            'prompt' => 'NPC: "Find my lost ___." Which word completes the quest line?',
            'options' => ['file', 'sudo', 'chmod'],
            'correct_index' => 0,
        ],
        3 => [
            'prompt' => 'Elder: "Please ___ a new directory for the festival." Which verb fits?',
            'options' => ['create', 'delete', 'deny'],
            'correct_index' => 0,
        ],
        5 => [
            'prompt' => 'Guide: "Do not ___ system files without a backup." Which word warns about careless reorganization?',
            'options' => ['move', 'list', 'print'],
            'correct_index' => 0,
        ],
        7 => [
            'prompt' => 'Ranger: "We need to ___ the hidden map in deep subdirectories." Which verb describes using find?',
            'options' => ['search', 'chmod', 'kill'],
            'correct_index' => 0,
        ],
        9 => [
            'prompt' => 'Guardian: "Before crossing, tell me who the ___ of temple.conf is." Which word is missing?',
            'options' => ['owner', 'process', 'folder'],
            'correct_index' => 0,
        ],
        11 => [
            'prompt' => 'Door inscription: "You do not have ___ to this gate." Which word completes the classic message?',
            'options' => ['access', 'memory', 'copy'],
            'correct_index' => 0,
        ],
        13 => [
            'prompt' => 'Foreman: "List every ___ blocking the mineral tracks." Which word describes what ps shows?',
            'options' => ['process', 'directory', 'permission'],
            'correct_index' => 0,
        ],
        15 => [
            'prompt' => 'Engineer: "RAM ___ keeps growing — we have a leak." Which word describes memory consumption?',
            'options' => ['usage', 'owner', 'path'],
            'correct_index' => 0,
        ],
        17 => [
            'prompt' => 'Supervisor: "Check CPU ___ before restarting the core drill." Which word fits with top?',
            'options' => ['usage', 'group', 'touch'],
            'correct_index' => 0,
        ],
    ];

    /** @var array<int, array{prompt: string, options: list<string>, correct_index: int}> */
    private const COMMAND_CONTEXT = [
        2 => [
            'prompt' => 'NPC: "Open the config folder, please." Which command moves you to the right directory?',
            'options' => ['cd', 'ls', 'kill'],
            'correct_index' => 0,
        ],
        4 => [
            'prompt' => 'You must duplicate map.txt before the fog erases the trails. Which command do you use?',
            'options' => ['cp', 'mv', 'rm'],
            'correct_index' => 0,
        ],
        6 => [
            'prompt' => 'Corrupted files block the main trail. Which command deletes a file?',
            'options' => ['rm', 'mkdir', 'pwd'],
            'correct_index' => 0,
        ],
        8 => [
            'prompt' => 'A gate requires read permission on the notice board. Which command adjusts permissions?',
            'options' => ['chmod', 'touch', 'find'],
            'correct_index' => 0,
        ],
        10 => [
            'prompt' => 'Only your party (devs group) should cross the rock gate. Which command changes a file\'s group?',
            'options' => ['chown', 'ps', 'top'],
            'correct_index' => 0,
        ],
        12 => [
            'prompt' => 'You need elevated privileges to repair the summit mechanism. Which command do you run?',
            'options' => ['sudo', 'ls', 'cp'],
            'correct_index' => 0,
        ],
        14 => [
            'prompt' => 'CPU is at 100% and the extractors stop. Which command monitors processes in real time?',
            'options' => ['top', 'mkdir', 'chmod'],
            'correct_index' => 0,
        ],
        16 => [
            'prompt' => 'A zombie process prevents restarting the machinery. Which command sends a signal to a PID?',
            'options' => ['kill', 'cd', 'touch'],
            'correct_index' => 0,
        ],
    ];

    /** @var array<int, array{prompt: string, options: list<string>, correct_index: int}> */
    private const TERM_MEANING = [
        1 => [
            'prompt' => 'In Linux navigation, which option best describes the term "path"?',
            'options' => [
                'The location of a file or directory in the filesystem',
                'A virus that only affects empty folders',
                'The default username for the root account',
            ],
            'correct_index' => 0,
        ],
        3 => [
            'prompt' => 'What is the main difference between mkdir and touch in this quest?',
            'options' => [
                'mkdir creates directories; touch creates empty files or updates timestamps',
                'mkdir deletes files; touch copies folders',
                'Both commands do exactly the same thing',
            ],
            'correct_index' => 0,
        ],
        5 => [
            'prompt' => 'When moving files between paths, what distinguishes an absolute path from a relative one?',
            'options' => [
                'An absolute path starts from / (root); a relative path depends on the current directory',
                'A relative path is always longer than an absolute path',
                'Only absolute paths can use mv',
            ],
            'correct_index' => 0,
        ],
        7 => [
            'prompt' => 'What is find used for in the deep directory forest?',
            'options' => [
                'Search for files and directories by name, type, or criteria in the tree',
                'Change permissions for every user on the system',
                'Kill processes that consume too much CPU',
            ],
            'correct_index' => 0,
        ],
        9 => [
            'prompt' => 'In chown, what does the "owner" of a file represent?',
            'options' => [
                'The user who has primary control over the file',
                'The process with the highest memory usage',
                'The parent folder of the root directory',
            ],
            'correct_index' => 0,
        ],
        11 => [
            'prompt' => 'When the system says "Permission denied", what does that technically imply?',
            'options' => [
                'Your user lacks the required permissions for that operation',
                'The disk is full and cannot write any more data',
                'The command you typed does not exist on Linux',
            ],
            'correct_index' => 0,
        ],
        13 => [
            'prompt' => 'What key information does ps give you about a process?',
            'options' => [
                'PID, state, and the command associated with the process',
                'The owner user\'s password',
                'The exact size of the hard drive',
            ],
            'correct_index' => 0,
        ],
        15 => [
            'prompt' => 'During a "memory leak", what happens to RAM?',
            'options' => [
                'A process allocates memory and never releases it, consuming RAM over time',
                'RAM physically shrinks due to excessive heat',
                'The system automatically deletes all files in the home directory',
            ],
            'correct_index' => 0,
        ],
        17 => [
            'prompt' => 'Before using kill, why is it good practice to check with ps or top?',
            'options' => [
                'Confirm the correct PID and understand which process you are terminating',
                'Because kill only works after running ls three times',
                'To convert the process into a text file',
            ],
            'correct_index' => 0,
        ],
    ];

    /** @var array<int, array{prompt: string, options: list<string>, correct_index: int}> */
    private const SCENARIO = [
        2 => [
            'prompt' => 'You are in /home/village and the NPC asks to open inventory/config. What is the most logical sequence?',
            'options' => [
                'cd inventory/config then ls to verify the contents',
                'rm -rf / to clean up and start from scratch',
                'kill the NPC process so they stop asking for folders',
            ],
            'correct_index' => 0,
        ],
        4 => [
            'prompt' => 'You must copy ancient-map.txt to backup/ before the fog arrives. What approach is correct?',
            'options' => [
                'cp ancient-map.txt backup/ and verify with ls backup/',
                'mv ancient-map.txt /dev/null without checking',
                'chmod 777 ancient-map.txt so the fog cannot touch it',
            ],
            'correct_index' => 0,
        ],
        6 => [
            'prompt' => 'Corrupted files sit in trail/. How do you clean up without wiping the whole forest?',
            'options' => [
                'Identify corrupted files with ls/find and use rm only on those files',
                'rm -rf / trail and reboot the router',
                'sudo touch / to replace the filesystem',
            ],
            'correct_index' => 0,
        ],
        8 => [
            'prompt' => 'A notice board has permissions ---------- and nobody can read it. What do you do first?',
            'options' => [
                'ls -l to inspect current permissions, then chmod to add read where needed',
                'kill the notice board process',
                'mv the board to /proc and wait',
            ],
            'correct_index' => 0,
        ],
        10 => [
            'prompt' => 'Your party (devs group) needs to read gate.key but others must not. What is a solid strategy?',
            'options' => [
                'Adjust the group with chown and group permissions with chmod (e.g. read for the group)',
                'Set 777 on the entire system to avoid problems',
                'Rename the file to sudo and ignore permissions',
            ],
            'correct_index' => 0,
        ],
        12 => [
            'prompt' => 'Repairing the summit mechanism requires editing a system file. What is a responsible workflow?',
            'options' => [
                'Use sudo only for the required operation, verify the change, and minimize privileges',
                'Share the root password with the whole team in chat',
                'Disable sudo permanently for "more security"',
            ],
            'correct_index' => 0,
        ],
        14 => [
            'prompt' => 'CPU at 100%: top shows an unknown process consuming resources. What is the prudent next step?',
            'options' => [
                'Investigate what the process does, confirm the impact, then decide if kill is needed',
                'kill -9 every listed process immediately',
                'Shut down the mine and document nothing',
            ],
            'correct_index' => 0,
        ],
        16 => [
            'prompt' => 'A zombie process blocks restarting the drill. What balanced action do you take?',
            'options' => [
                'Identify the PID with ps, understand the zombie parent, and terminate the correct process',
                'Physically reboot the datacenter without checking processes',
                'Create a new zombie with touch to "balance" the system',
            ],
            'correct_index' => 0,
        ],
    ];
}
