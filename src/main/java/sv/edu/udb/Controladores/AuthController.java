package sv.edu.udb.Controladores;

import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpHeaders;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.security.authentication.AuthenticationManager;
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken;
import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;
import sv.edu.udb.Modelos.User;
import sv.edu.udb.Servicios.JwtService;
import sv.edu.udb.Servicios.UserService;
import sv.edu.udb.dto.request.AuthRequestDto;
import sv.edu.udb.dto.request.RefreshTokenRequestDto;
import sv.edu.udb.dto.request.UsuarioRequestDto;
import sv.edu.udb.dto.response.AuthResponseDto;
import sv.edu.udb.dto.response.RefreshTokenResponseDto;

@RequiredArgsConstructor
@Tag(name = "Autenticacion", description = "Endpoints de Login y Registro de usuarios")
@RestController
@RequestMapping("/api/auth")
public class AuthController {

    private final UserService userService;
    // Objeto que se encarga de codificar las contraseñas para no mandarlas asi nomas
    private final PasswordEncoder passwordEncoder;
    private final JwtService jwtService;
    // Este objeto nos va a validar que el usuario y contraseña en el token esten bien
    private final AuthenticationManager authenticationManager;

    @Operation(summary = "Iniciar sesion con usuario y contraseña")
    @PostMapping("/login")
    public ResponseEntity<AuthResponseDto> login(@Valid @RequestBody AuthRequestDto requestDto) {
        //Envolvemos las credenciales y el username en un objeto que Spring Security puede entender
        authenticationManager.authenticate(new UsernamePasswordAuthenticationToken(
                requestDto.getUsername(),
                requestDto.getPassword()));
        AuthResponseDto authResponseDto = AuthResponseDto.builder()
                .username(requestDto.getUsername())
                .token(jwtService.generarToken(requestDto.getUsername()))
                .refreshToken(jwtService.generarRefreshToken(requestDto.getUsername()))
                .build();
        return ResponseEntity.ok(authResponseDto);
    }


    //Cuando se registra, se crea un JSON para que entre de un solo al sistema
    @Operation(summary = "Registrar un nuevo usuario")
    @PostMapping("/register")
    public ResponseEntity<AuthResponseDto> register(@Valid @RequestBody UsuarioRequestDto requestDto){
        if(userService.verificarUsuarioExiste(requestDto.getUsername())){
            throw new RuntimeException("El usuario con nombre de usuario " + requestDto.getUsername() + " ya existe");
        }
        requestDto.setPassword(passwordEncoder.encode(requestDto.getPassword()));
        userService.registrarUsuario(toUser(requestDto));
        AuthResponseDto responseDto = new AuthResponseDto();
        responseDto.setUsername(requestDto.getUsername());
        responseDto.setToken(jwtService.generarToken(requestDto.getUsername()));
        responseDto.setRefreshToken(jwtService.generarRefreshToken(requestDto.getUsername()));
        return ResponseEntity.status(HttpStatus.CREATED).body(responseDto);
    }

    // Nos dan el refreshToken y le devolvemos un token renovado
    @Operation(summary = "Refrescar token JWT usando refresh token")
    @PostMapping("/refresh-token")
    public ResponseEntity<RefreshTokenResponseDto> refreshToken(@Valid @RequestBody RefreshTokenRequestDto requestDto) {
        String username = jwtService.extraerUsername(requestDto.getRefreshToken());
        String newToken = jwtService.generarToken(username);
        RefreshTokenResponseDto responseDto = RefreshTokenResponseDto.builder()
                .token(newToken)
                .build();
        return ResponseEntity.ok(responseDto);
    }

    //Metodo auxiliar
    private User toUser(UsuarioRequestDto requestDto) {
        return User.builder()
                .username(requestDto.getUsername())
                .firstname(requestDto.getFirstname())
                .lastname(requestDto.getLastname())
                .age(requestDto.getAge())
                .password(requestDto.getPassword())
                .build();
    }

}
