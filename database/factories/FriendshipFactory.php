<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Friendship>
 */
class FriendshipFactory extends Factory
{
    protected $model = Friendship::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'friend_id' => User::factory(),
            'status' => $this->faker->randomElement([
                Friendship::STATUS_PENDING,
                Friendship::STATUS_ACCEPTED,
                Friendship::STATUS_BLOCKED,
                Friendship::STATUS_DECLINED,
            ]),
            'message' => $this->faker->optional(0.3)->sentence(),
            'accepted_at' => function (array $attributes) {
                return $attributes['status'] === Friendship::STATUS_ACCEPTED 
                    ? $this->faker->dateTimeBetween('-6 months', 'now')
                    : null;
            },
        ];
    }

    /**
     * Indicate that the friendship is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Friendship::STATUS_PENDING,
            'accepted_at' => null,
        ]);
    }

    /**
     * Indicate that the friendship is accepted.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Friendship::STATUS_ACCEPTED,
            'accepted_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    /**
     * Indicate that the friendship is blocked.
     */
    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Friendship::STATUS_BLOCKED,
            'accepted_at' => null,
        ]);
    }

    /**
     * Indicate that the friendship is declined.
     */
    public function declined(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Friendship::STATUS_DECLINED,
            'accepted_at' => null,
        ]);
    }

    /**
     * Create a friendship with a message.
     */
    public function withMessage(string $message): static
    {
        return $this->state(fn (array $attributes) => [
            'message' => $message,
        ]);
    }
}
