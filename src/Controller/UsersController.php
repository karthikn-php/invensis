<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Cache\Cache;
use Cake\I18n\Date;
use Cake\I18n\Time;

/**
 * Users Controller
 *
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 * @author Karthikeyan C <karthikn.php@gmail.com>
 * @package App\Controller
 */
class UsersController extends AppController
{
    /**
     * Add User
     * @return \Cake\Http\Response|null
     * @author Karthikeyan C <karthikn.php@gmail.com>
     */
    public function add()
    {
        $user       =   $this->Users->newEmptyEntity();
        $requestData=   $this->request->getData();
        if ($this->request->is('post')) {
            $requestData['created_at'] = Time::now()->format(Date::DEFAULT_TO_STRING_FORMAT);
            $user   =   $this->Users->patchEntity($user, $requestData);
            $errors =   [];
            if (!$user->hasErrors()) {
                try {
                    $this->Users->save($user);
                    return $this->response
                        ->withType('json')
                        ->withStringBody(
                            json_encode([
                                'message' => 'The user has been saved.'
                            ])
                        );
                }
                catch (\Exception $exception) {
                    \Cake\Log\Log::error(print_r($requestData, true));
                    \Cake\Log\Log::error($exception->getMessage());
                    return $this->response
                        ->withType('json')
                        ->withStringBody(
                            json_encode([
                                'message' => 'The user could not be saved. Please, try again.'
                            ])
                        );
                }
            }
            else {
                foreach ($user->getErrors() as $fieldName => $fieldHasErrors){
                    $errors[$fieldName] = implode(', ', $fieldHasErrors);
                }
            }
            return $this->response
                ->withType('json')
                ->withStringBody(
                    json_encode([
                        'errors' => $errors,
                        'message' => 'The user could not be saved. Please, try again.'
                    ])
                );
        }
        return $this->response
            ->withStatus(405, 'Method Not Allowed')
            ->withType('json')
            ->withStringBody(
                json_encode([
                    'message' => 'The Requested Method Not Allowed'
                ])
            );
    }

    /**
     * @return \Cake\Http\Response|null
     * @author Karthikeyan C <karthikn.php@gmail.com>
     */
    public function list()
    {
        $users = $this->paginate($this->Users);
        return $this->response
            ->withType('json')
            ->withStringBody(
                json_encode([
                    'users' => $users
                ])
            );
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        if ($this->request->is('get')) {
            try {
                $user = $this->Users->get($id, [
                    'contain' => [],
                ]);
                return $this->response
                    ->withType('json')
                    ->withStringBody(
                        json_encode([
                            'user' => $user
                        ])
                    );
            }
            catch (\Exception $exception) {
                \Cake\Log\Log::error($exception->getMessage());
                return $this->response
                    ->withType('json')
                    ->withStringBody(
                        json_encode([
                            'message' => 'The requested user not found'
                        ])
                    );
            }
        }
        return $this->response
            ->withStatus(405, 'Method Not Allowed')
            ->withType('json')
            ->withStringBody(
                json_encode([
                    'message' => 'The Requested Method Not Allowed'
                ])
            );
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        if ($this->request->is('post')) {
            try {
                $user = $this->Users->get($id, [
                    'contain' => [],
                ]);
                $user = $this->Users->patchEntity($user, $this->request->getData());
                if ($this->Users->save($user)) {
                    return $this->response
                        ->withType('json')
                        ->withStringBody(
                            json_encode([
                                'message' => 'The user profile has been updated.'
                            ])
                        );
                }
            }
            catch (\Exception $exception) {
                return $this->response
                    ->withType('json')
                    ->withStringBody(
                        json_encode([
                            'message' => 'The user does not exist.'
                        ])
                    );
            }
            return $this->response
                ->withType('json')
                ->withStringBody(
                    json_encode([
                        'message' => 'The user profile has not be saved. Please, try again.'
                    ])
                );
        }
        return $this->response
            ->withStatus(405, 'Method Not Allowed')
            ->withType('json')
            ->withStringBody(
                json_encode([
                    'message' => 'The Requested Method Not Allowed'
                ])
            );
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        if ($this->request->is('delete')) {
            $user = $this->Users->get($id);
            if ($this->Users->delete($user)) {
                return $this->response
                    ->withType('json')
                    ->withStringBody(
                        json_encode([
                            'message' => 'The user has been deleted.'
                        ])
                    );
            } else {
                return $this->response
                    ->withType('json')
                    ->withStringBody(
                        json_encode([
                            'message' => 'The user could not be deleted. Please, try again.'
                        ])
                    );
            }
        }
        return $this->response
            ->withStatus(405, 'Method Not Allowed')
            ->withType('json')
            ->withStringBody(
                json_encode([
                    'message' => 'The Requested Method Not Allowed'
                ])
            );
    }

    /**
     * @return \Cake\Http\Response|null
     * @author Karthikeyan C <karthikn.php@gmail.com>
     */
    public function search()
    {
        $searchText = $this->request->getQuery('text') ?? $this->request->getData('text');
        if ($this->request->is(['get', 'post'])) {
            $users = Cache::read('users');
            if (!empty($searchText)) {
                $users = $this->Users
                            ->find()
                            ->where([
                                'OR' => [
                                    "INSTR(email, '{$searchText}') > 0",
                                    "INSTR(full_name, '{$searchText}') > 0",
                                ]
                            ])
                            ->disableHydration()
                            ->toArray();
                Cache::write('users', $users);
            }
            //If Searched User retrieved
            if (!empty($users)) {
                return $this->response
                    ->withType('json')
                    ->withStringBody(
                        json_encode([
                            'user' => $users
                        ])
                    );
            }
            //If Search term could not find any user
            return $this->response
                ->withType('json')
                ->withStringBody(
                    json_encode([
                        'message' => 'Unable to find the user. Please search different text.'
                    ])
                );
        }
        return $this->response
            ->withStatus(405, 'Method Not Allowed')
            ->withType('json')
            ->withStringBody(
                json_encode([
                    'message' => 'The Requested Method Not Allowed'
                ])
            );
    }
}
