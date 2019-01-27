<?php

namespace Tests\Feature;

use App\Http\Resources\SnsResponseResource;
use App\Models\SnsResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SNSResponseTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class)->create(['password' => '123456']);
    }

    /** @test */
    public function it_will_show_all_sns_response()
    {
        $this->actingAs($this->user);

        $sns_responses = factory(SnsResponse::class, 30)->create();

        $sns_response = $sns_responses->first();

        $response = $this->call(
            'GET',
            route('api.sns.index', ['type' => 'all'])
        );

        $response->assertStatus(200);

        $resource = (new SnsResponseResource($sns_response))->jsonSerialize();

        $this->assertArraySubset([
            'uuid' => $sns_response->uuid,
            'email' => $sns_response->email,
            'notification_type' => $sns_response->notification_type,
            'type' => $sns_response->type,
            'source_email' => $sns_response->source_email,
            'source_arn' => $sns_response->source_arn,
            'datetime_payload' => $sns_response->datetime_payload->toDateTimeString()
        ], $resource);
    }

    /** @test */
    public function it_will_delete_a_sns_response()
    {
        $this->actingAs($this->user);

        $sns_response =  factory(SnsResponse::class)->create();

        $response = $this->call(
            'DELETE',
            route('api.sns.destroy', $sns_response)
        );

        $response->assertJsonStructure([
            'status',
            'message'
        ]);

        $response->assertExactJson([
            'status' => 'success',
            'message' => 'Resource Deleted.'
        ]);

        $response->assertStatus(200);
    }
}
