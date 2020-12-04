<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Cache\Cache;
use Cake\I18n\Date;
use Cake\I18n\Time;

/**
 * Posts Controller
 *
 * @method \App\Model\Entity\Post[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class PostsController extends AppController
{

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $post       =   $this->Posts->newEmptyEntity();
        $requestData=   $this->request->getData();
        // Since Auth implementation not there we`re fetching rand user_id`s to make random user post
        $this->loadModel('Users');
        $userData   =   $this->Users->find()->select(['id'])->orderDesc('id')->first()->toArray();
        if (!empty($userData)) {
            $requestData['user_id']     = rand(1, $userData['id']);
        }
        $requestData['published_at']= Time::now()->format(Date::DEFAULT_TO_STRING_FORMAT);
        if ($this->request->is('post')) {
            $post   =   $this->Posts->patchEntity($post, $requestData);
            $errors =   [];
            if (!$post->hasErrors()) {
                try {
                    $this->Posts->save($post);
                    return $this->response
                        ->withType('json')
                        ->withStringBody(
                            json_encode([
                                'message' => 'The post has been saved.'
                            ])
                        );
                }
                catch (\Exception $exception) {
                    \Cake\Log\Log::error(print_r($this->request->getData(), true));
                    \Cake\Log\Log::error($exception->getMessage());
                    return $this->response
                        ->withType('json')
                        ->withStringBody(
                            json_encode([
                                'message' => 'The post could not be saved. Please, try again.'
                            ])
                        );
                }
            }
            else {
                foreach ($post->getErrors() as $fieldName => $fieldHasErrors){
                    $errors[$fieldName] = implode(', ', $fieldHasErrors);
                }
            }
            return $this->response
                ->withType('json')
                ->withStringBody(
                    json_encode([
                        'errors' => $errors,
                        'message' => 'The post could not be saved. Please, try again.'
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
     * List Posts
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function list()
    {
        $posts = $this->paginate($this->Posts);
        return $this->response
            ->withType('json')
            ->withStringBody(
                json_encode([
                    'users' => $posts
                ])
            );
    }

    /**
     * View method
     *
     * @param string|null $id Post id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $post = $this->Posts->get($id, [
            'contain' => [],
        ]);
        $this->set(compact('post'));
    }


    /**
     * Edit method
     *
     * @param string|null $id Post id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        if ($this->request->is('post')) {
            try {
                $post = $this->Posts->get($id, [
                    'contain' => [],
                ]);
                $post = $this->Posts->patchEntity($post, $this->request->getData());
                if ($this->Posts->save($post)) {
                    return $this->response
                        ->withType('json')
                        ->withStringBody(
                            json_encode([
                                'message' => 'The post profile has been updated.'
                            ])
                        );
                }
                else {
                    return $this->response
                        ->withType('json')
                        ->withStringBody(
                            json_encode([
                                'message' => 'Nothing has been updated in the post.'
                            ])
                        );
                }
            }
            catch (\Exception $exception) {
                    return $this->response
                        ->withType('json')
                        ->withStringBody(
                            json_encode([
                                'message' => 'The post does not exist.'
                            ])
                        );
                }
            return $this->response
                ->withType('json')
                ->withStringBody(
                    json_encode([
                        'message' => 'The post profile has not been updated.'
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
     * @param string|null $id Post id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        if ($this->request->is('delete')) {
            $post = $this->Posts->get($id);
            if ($this->Posts->delete($post)) {
                return $this->response
                    ->withType('json')
                    ->withStringBody(
                        json_encode([
                            'message' => 'The post has been deleted.'
                        ])
                    );
            } else {
                return $this->response
                    ->withType('json')
                    ->withStringBody(
                        json_encode([
                            'message' => 'The post could not be deleted. Please, try again.'
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
            $posts = Cache::read('posts');
            if (!empty($searchText)) {
                $posts  =   $this->Posts
                                ->find()
                                ->where([
                                    'OR' => [
                                        "INSTR(title, '{$searchText}') > 0",
                                        "INSTR(subtitle, '{$searchText}') > 0",
                                        "INSTR(content, '{$searchText}') > 0",
                                    ]
                                ])
                                ->disableHydration()
                                ->toArray();
                Cache::write('posts', $posts);
            }
            //If Searched User retrieved
            if (!empty($posts)) {
                return $this->response
                    ->withType('json')
                    ->withStringBody(
                        json_encode([
                            'posts' => $posts
                        ])
                    );
            }
            //If Search term could not find any user
            return $this->response
                ->withType('json')
                ->withStringBody(
                    json_encode([
                        'message' => 'Unable to find the posts. Please search different text.'
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
