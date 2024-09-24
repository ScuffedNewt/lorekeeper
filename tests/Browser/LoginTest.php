<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\User\User;
use App\Models\Rank\Rank;

class LoginTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $rank;

    /**
     * Set up necessary data before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->rank = Rank::factory()->create();
    }


    /**
     * tests that a user can login
     */
    public function testUserCanLogin()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                    ->type('email', $user->email)
                    ->type('password', 'password')
                    ->press('Login')
                    ->assertPathIs('/')
                    ->assertSee('Welcome');
        });
    }
}
