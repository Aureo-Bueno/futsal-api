<?php

declare(strict_types=1);

namespace Futsal\Contracts;

final class EventType
{
    public const TEAM_CREATED = 'team.created';

    public const TEAM_UPDATED = 'team.updated';

    public const TEAM_DEACTIVATED = 'team.deactivated';

    public const PLAYER_REGISTERED = 'player.registered';

    public const PLAYER_TRANSFERRED = 'player.transferred';

    public const PLAYER_REMOVED = 'player.removed';

    public const MATCH_SCHEDULED = 'match.scheduled';

    public const MATCH_STARTED = 'match.started';

    public const MATCH_GOAL_SCORED = 'match.goal_scored';

    public const MATCH_FINISHED = 'match.finished';

    public const MATCH_CANCELLED = 'match.cancelled';
}
