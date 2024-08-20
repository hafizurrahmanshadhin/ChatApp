# ChatApp

php artisan reverb:start

php artisan reverb:start --debug

-------------------

1. Define API Routes:

Add the following routes to routes/api.php:

```php
<?php
use App\Http\Controllers\Api\ChatController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [ChatController::class, 'getUsers']);
    Route::get('/messages/{userId}', [ChatController::class, 'getMessages']);
    Route::post('/messages', [ChatController::class, 'sendMessage']);
});
```

Create API Controllers:

Generate a new controller for handling chat-related API requests:

```php
php artisan make:controller Api/ChatController
```

Then, implement the methods in ChatController.php:

```php
<?php
namespace App\Http\Controllers\Api;

use App\Events\MessageSendEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Http\Resources\UserResource;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function getUsers()
    {
        $users = User::where('id', '!=', auth()->id())->get();
        return UserResource::collection($users);
    }

    public function getMessages($userId)
    {
        $messages = Message::where(function ($query) use ($userId) {
            $query->where('sender_id', auth()->id())
                  ->where('receiver_id', $userId);
        })->orWhere(function ($query) use ($userId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', auth()->id());
        })->with('sender:id,name', 'receiver:id,name')->get();

        return MessageResource::collection($messages);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        $chatMessage = new Message();
        $chatMessage->sender_id = auth()->id();
        $chatMessage->receiver_id = $request->receiver_id;
        $chatMessage->message = $request->message;
        $chatMessage->save();

        broadcast(new MessageSendEvent($chatMessage))->toOthers();

        return new MessageResource($chatMessage);
    }
}
```

Create Resources:

Generate resources for formatting the data:

```php
php artisan make:resource UserResource
php artisan make:resource MessageResource
```

Then, implement the resources in UserResource.php and MessageResource.php:

UserResource.php:

```php
<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
```

MessageResource.php:

```php
<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'sender' => new UserResource($this->sender),
            'receiver' => new UserResource($this->receiver),
            'created_at' => $this->created_at,
        ];
    }
}
```

Update Models:

Ensure your Message model has the necessary relationships:

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['sender_id', 'receiver_id', 'message'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
```
